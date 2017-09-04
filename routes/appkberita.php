<?php
/**
 * Validasi
 * @param  array $data
 * @param  array $custom
 * @return array
 */
function validasi($data, $custom = array())
{
    $validasi = array(
        'nama' => 'required',
    );

    $cek = validate($data, $validasi, $custom);
    return $cek;
}

/**
 * get list kategori berita
 */
$app->get('/appkberita/index', function ($request, $response) {
    $params = $_REQUEST;

    $sort   = "id DESC";
    $offset = isset($params['offset']) ? $params['offset'] : 0;
    $limit  = isset($params['limit']) ? $params['limit'] : 10;

    $db = $this->db;

    $db->select("*")
        ->from('kategori_berita');

    /** set parameter */
    if (isset($params['filter'])) {

        $filter = (array) json_decode($params['filter']);

        if (isset($filter['nama'])) {
            /** Get field */
            $sql  = $this->db;
            $list = $sql->get_field('kategori_berita');

            foreach ($list as $key => $val) {
                $db->orWhere('kategori_berita.' . $val, 'LIKE', $filter['nama']);
            }
        }
    }

    /** Set limit */
    if (!empty($limit)) {
        $db->limit($limit);
    }

    /** Set offset */
    if (!empty($offset)) {
        $db->offset($offset);
    }

    /** Set sorting */
    if (!empty($params['sort'])) {
        $db->sort($sort);
    }

    $models    = $db->findAll();
    $totalItem = $db->count();

    return successResponse($response, ['list' => $models, 'totalItems' => $totalItem]);
});

/**
 * save kategori berita
 */
$app->post('/appkberita/save', function ($request, $response) {
    $data = $request->getParams();

    $data['nama'] = isset($data['nama']) ? $data['nama'] : '';

    $db = $this->db;

    $validasi = validasi($data);

    if ($validasi === true) {
        if (isset($data['id'])) {
            $model = $db->update("kategori_berita", $data, ['id' => $data['id']]);
        } else {
            $model = $db->insert("kategori_berita", $data);
        }
        return successResponse($response, $model);
    }
    return unprocessResponse($response, $validasi);
});

/**
 * delete berita
 */
$app->delete('/appkberita/delete/{id}', function ($request, $response) {
    $db = $this->db;

    try {
        $delete = $db->delete('kategori_berita', array('id' => $request->getAttribute('id')));
        return successResponse($response, ['data berhasil dihapus']);
    } catch (Exception $e) {
        return unprocessResponse($response, ['data gagal dihapus']);
    }
});
