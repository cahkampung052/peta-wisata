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
        'nama'     => 'required',
        'username' => 'required',
        // 'level'    => 'required',
        'alamat'   => 'required',
        'email'    => 'required',
        'no_tlp'   => 'required',
    );

    $cek = validate($data, $validasi, $custom);
    return $cek;
}

/**
 * ValidasiProfil
 * @param  array $data
 * @param  array $custom
 * @return array
 */
function validasiProfil($data, $custom = array())
{
    $validasi = array(
        'nama'     => 'required',
        'username' => 'required',
    );

    $cek = validate($data, $validasi, $custom);
    return $cek;
}

/**
 * get user detail for update profile
 */
$app->get('/appuser/view', function ($request, $response) {
    $db = $this->db;

    $data = $db->find('select id, nama, username from user where id = "' . $_SESSION['user']['id'] . '"');

    return successResponse($response, $data);
});

/**
 * get user list
 */
$app->get('/appuser/index', function ($request, $response) {
    $params = $_REQUEST;

    $sort   = "id DESC";
    $offset = isset($params['offset']) ? $params['offset'] : 0;
    $limit  = isset($params['limit']) ? $params['limit'] : 10;

    $db = $this->db;

    $db->select("*")
        ->from('user');

    /** set parameter */
    if (isset($params['filter'])) {

        $filter = (array) json_decode($params['filter']);

        if (isset($filter['nama'])) {
            /** Get field */
            $sql  = $this->db;
            $list = $sql->get_field('user');

            foreach ($list as $key => $val) {
                $db->orWhere($val, 'LIKE', $filter['nama']);
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
 * create user
 */
$app->post('/appuser/create', function ($request, $response) {
    $data = $request->getParams();

    $data['nama'] = isset($data['nama']) ? $data['nama'] : '';
    $data['username'] = isset($data['username']) ? $data['username'] : '';
    $data['alamat'] = isset($data['alamat']) ? $data['alamat'] : '';
    $data['email'] = isset($data['email']) ? $data['email'] : '';
    $data['no_tlp'] = isset($data['no_tlp']) ? $data['no_tlp'] : '';

    $db = $this->db;

    $validasi = validasi($data, ['password' => 'required']);

    if ($validasi === true) {
        $data['password'] = sha1($data['password']);
        try {
            $model = $db->insert("user", $data);
            return successResponse($response, $model);
        } catch (Exception $e) {
            return unprocessResponse($response, ['data gagal disimpan']);
        }
    }
    return unprocessResponse($response, $validasi);
});

/**
 * update user profile
 */
$app->post('/appuser/updateprofil', function ($request, $response) {
    $data = $request->getParams();
    $id   = $_SESSION['user']['id'];

    $data['nama'] = isset($data['nama']) ? $data['nama'] : '';
    $data['username'] = isset($data['username']) ? $data['username'] : '';
    $data['alamat'] = isset($data['alamat']) ? $data['alamat'] : '';
    $data['email'] = isset($data['email']) ? $data['email'] : '';
    $data['no_tlp'] = isset($data['no_tlp']) ? $data['no_tlp'] : '';

    $db = $this->db;

    if (!empty($data['password'])) {
        $data['password'] = sha1($model['password']);
    } else {
        unset($data['password']);
    }

    $validasi = validasiProfil($data);

    if ($validasi === true) {
        try {
            $model = $db->update("user", $data, array('id' => $id));
            return successResponse($response, $model);
        } catch (Exception $e) {
            return unprocessResponse($response, ['data gagal disimpan']);
        }
    }
    return unprocessResponse($response, $validasi);
});

/**
 * update user
 */
$app->post('/appuser/update', function ($request, $response) {
    $data = $request->getParams();

    $data['nama'] = isset($data['nama']) ? $data['nama'] : '';
    $data['username'] = isset($data['username']) ? $data['username'] : '';
    $data['alamat'] = isset($data['alamat']) ? $data['alamat'] : '';
    $data['email'] = isset($data['email']) ? $data['email'] : '';
    $data['no_tlp'] = isset($data['no_tlp']) ? $data['no_tlp'] : '';

    $db = $this->db;

    if (!empty($data['password'])) {
        $data['password'] = sha1($data['password']);
    } else {
        unset($data['password']);
    }

    $validasi = validasi($data);

    if ($validasi === true) {
        try {
            $model = $db->update("user", $data, array('id' => $data['id']));
            return successResponse($response, $model);
        } catch (Exception $e) {
            return unprocessResponse($response, ['data gagal disimpan']);
        }
    }
    return unprocessResponse($response, $validasi);
});

/**
 * delete user
 */
$app->delete('/appuser/delete/{id}', function ($request, $response) {
    $db = $this->db;

    try {
        $delete = $db->delete('user', array('id' => $request->getAttribute('id')));
        return successResponse($response, ['data berhasil dihapus']);
    } catch (Exception $e) {
        return unprocessResponse($response, ['data gagal dihapus']);
    }
});
