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
        'judul'              => 'required',
        'konten'             => 'required',
        'kategori_berita_id' => 'required',
    );

    $cek = validate($data, $validasi, $custom);
    return $cek;
}

/**
 * get list berita
 */
$app->get('/appberita/index', function ($request, $response) {
    $params = $_REQUEST;

    $sort   = "id DESC";
    $offset = isset($params['offset']) ? $params['offset'] : 0;
    $limit  = isset($params['limit']) ? $params['limit'] : 10;

    $db = $this->db;

    $db->select("berita.*, kategori_berita.nama as kategori, user.nama as user")
        ->from('berita')
        ->leftJoin('kategori_berita', 'kategori_berita.id = berita.kategori_berita_id')
        ->leftJoin('user', 'user.id = berita.user_id');

    /** set parameter */
    if (isset($params['filter'])) {

        $filter = (array) json_decode($params['filter']);

        if (isset($filter['nama'])) {
            /** Get field */
            $sql  = $this->db;
            $list = $sql->get_field('berita');

            foreach ($list as $key => $val) {
                $db->orWhere('berita.' . $val, 'LIKE', $filter['nama']);
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
 * get kategori
 */
$app->get('/appberita/getkategori', function ($request, $response) {
    $db       = $this->db;
    $kategori = $db->findAll('select * from kategori_berita');

    return successResponse($response, $kategori);
});

/**
 * save berita
 */
$app->post('/appberita/save', function ($request, $response) {
    $data = $request->getParams();

    $db = $this->db;

    $validasi = validasi($data);

    if ($validasi === true) {
        if (isset($data['id'])) {
            $model = $db->update("berita", $data, ['id' => $data['id']]);
        } else {
            $data['user_id'] = $_SESSION['user']['id'];
            $data['tanggal'] = strtotime(date("Y-m-d H:i:s"));

            $model = $db->insert("berita", $data);
        }
        return successResponse($response, $model);
    }
    return unprocessResponse($response, $validasi);
});

/**
 * delete berita
 */
$app->delete('/appberita/delete/{id}', function ($request, $response) {
    $db = $this->db;

    try {
        $delete = $db->delete('berita', array('id' => $request->getAttribute('id')));
        return successResponse($response, ['data berhasil dihapus']);
    } catch (Exception $e) {
        return unprocessResponse($response, ['data gagal dihapus']);
    }
});
