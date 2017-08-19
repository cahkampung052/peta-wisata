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
        'nama'               => 'required',
        'alamat'             => 'required',
        'kategori_wisata_id' => 'required',
        'informasi'          => 'required',
    );

    $cek = validate($data, $validasi, $custom);
    return $cek;
}

/**
 * get list wisata
 */
$app->get('/appwisata/index', function ($request, $response) {
    $params = $_REQUEST;

    $sort   = "id DESC";
    $offset = isset($params['offset']) ? $params['offset'] : 0;
    $limit  = isset($params['limit']) ? $params['limit'] : 10;

    $db = $this->db;

    $db->select("wisata.*, kategori_wisata.nama as kategori")
        ->from('wisata')
        ->leftJoin('kategori_wisata', 'kategori_wisata.id = wisata.kategori_wisata_id');

    /** set parameter */
    if (isset($params['filter'])) {

        $filter = (array) json_decode($params['filter']);

        if (isset($filter['nama'])) {
            /** Get field */
            $sql  = $this->db;
            $list = $sql->get_field('wisata');

            foreach ($list as $key => $val) {
                $db->orWhere('wisata.' . $val, 'LIKE', $filter['nama']);
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

    foreach ($models as $key => $value) {
        $value->foto = getenv('SITE_IMG') . $value->foto;
    }

    return successResponse($response, ['list' => $models, 'totalItems' => $totalItem]);
});

/**
 * get kategori
 */
$app->get('/appwisata/getkategori', function ($request, $response) {
    $db       = $this->db;
    $kategori = $db->findAll('select * from kategori_wisata');

    return successResponse($response, $kategori);
});

/**
 * save wisata
 */
$app->post('/appwisata/save', function ($request, $response) {
    $data = $request->getParams();

    $db = $this->db;

    $validasi = validasi($data);

    if ($validasi === true) {
        if (isset($data['id'])) {
            if (isset($data['foto']) and is_url($data['foto']) == false) {
                $folder = getenv("IMG_PATH") . 'wisata/';;

                /** set foto wisata */
                $upload = base64toImg($data['foto'], $folder, date("ymdhis") . 'foto');
                if ($upload['status']) {
                    $data['foto'] = $folder . $upload['data'];
                } else {
                    return unprocessResponse($response, $upload['data']);
                }
            } else {
                unset($data['foto']);
            }

            $model = $db->update("wisata", $data, ['id' => $data['id']]);
        } else {
            $folder = getenv("IMG_PATH") . 'wisata/';;

            /** set foto wisata */
            if (isset($data['foto']) and !empty($data['foto'])) {
                $upload = base64toImg($data['foto'], $folder, date("ymdhis") . 'foto');
                if ($upload['status']) {
                    $data['foto'] = $folder . $upload['data'];
                } else {
                    return unprocessResponse($response, $upload['data']);
                }
            }

            $model = $db->insert("wisata", $data);
        }
        return successResponse($response, $model);
    }
    return unprocessResponse($response, $validasi);
});

/**
 * delete wisata
 */
$app->delete('/appwisata/delete/{id}', function ($request, $response) {
    $db = $this->db;

    try {
        $delete = $db->delete('wisata', array('id' => $request->getAttribute('id')));
        return successResponse($response, ['data berhasil dihapus']);
    } catch (Exception $e) {
        return unprocessResponse($response, ['data gagal dihapus']);
    }
});
