<?php
$app->get('/wisata/{id}/{alias}.html', function ($request, $response) {
    $db = $this->db;

    $db->select("wisata.*, kategori_wisata.icon, kategori_wisata.nama as kategori")
        ->from("wisata")
        ->leftJoin("kategori_wisata", "kategori_wisata.id = wisata.kategori_wisata_id")
        ->where("wisata.id", "=", $request->getAttribute('id'));

    $wisata = $db->find();

    $other = $db->findAll('select * from wisata where id != "' . $wisata->id . '" order by rand() DESC limit 5');

    foreach ($other as $key => $value) {
        $value->alias = urlParsing($value->nama);
    }

    $foto = [];
    if (isset($wisata->id) && !empty($wisata->hastag)) {
        $hastag = str_replace("#", "", $wisata->hastag);

        $client = new \GuzzleHttp\Client();
        $req    = $client->request('GET', 'https://www.instagram.com/explore/tags/' . $hastag . '?__a=1');

        if ($req->getStatusCode() == 200) {
            $res = json_decode($req->getBody(), true);
            foreach ($res['tag']['top_posts']['nodes'] as $key => $value) {
                if ($value['is_video'] == false) {
                    $foto[] = $value['thumbnail_src'];
                }
            };
        }
    }

    $wisata->nameUrl = $request->getAttribute('alias');

    return $this->view->render($response, 'view/wisata.html', [
        'wisata' => $wisata,
        'other'  => $other,
        'foto'   => $foto,
    ]);

})->setName('wisata');

$app->get('/', function ($request, $response) {

    $db = $this->db;

    $kategori = $db->findAll('select * from kategori_wisata');

    return $this->view->render($response, 'view/home.html', [
        'kategori' => $kategori,
    ]);
})->setName('index');

$app->get('/berita', function ($request, $response) {
    $db = $this->db;

    $db->select([
        'berita.*',
        'user.nama as user',
        'kategori_berita.nama as kategori',
    ])->from('berita')
        ->leftJoin('user', 'user.id = berita.user_id')
        ->leftJoin('kategori_berita', 'berita.kategori_berita_id = kategori_berita.id')
        ->orderBy('id DESC');

    $berita = $db->findAll();

    foreach ($berita as $key => $value) {
        $value->cuplikan = cuplikan($value->konten, 200);
        $value->img      = get_first_image($value->konten);
        $value->alias    = urlParsing($value->judul);
        $value->tanggal  = date("d/m/Y", $value->tanggal);
    }

    $populer = $db->findAll('select * from berita order by dibaca DESC limit 5');

    foreach ($populer as $key => $value) {
        $value->cuplikan = cuplikan($value->konten, 200);
        $value->img      = get_first_image($value->konten);
        $value->alias    = urlParsing($value->judul);
    }

    return $this->view->render($response, 'view/artikel.html', [
        'kategori' => 'Berita',
        'list'     => $berita,
        'populer'  => $populer,
    ]);
})->setName('berita');

$app->get('/artikel/{id}/{alias}.html', function ($request, $response) {
    $db = $this->db;

    $updateRead = $db->run('update berita set dibaca = dibaca + 1 where id = "' . $request->getAttribute('id') . '"');

    $db->select([
        'berita.*',
        'user.nama as user',
        'kategori_berita.nama as kategori',
    ])->from('berita')
        ->leftJoin('user', 'user.id = berita.user_id')
        ->leftJoin('kategori_berita', 'berita.kategori_berita_id = kategori_berita.id')
        ->where('berita.id', '=', $request->getAttribute('id'))
        ->orderBy('berita.id DESC');

    $artikel          = $db->find();
    $artikel->alias   = urlParsing($artikel->judul);
    $artikel->tanggal = date("d M Y", $artikel->tanggal);

    $populer = $db->findAll('select * from berita where kategori_berita_id = 1 order by dibaca DESC limit 7');
    foreach ($populer as $key => $value) {
        $value->cuplikan = cuplikan($value->konten, 200);
        $value->img      = get_first_image($value->konten);
        $value->alias    = urlParsing($value->judul);
    }

    return $this->view->render($response, 'view/detail.html', [
        'artikel' => $artikel,
        'populer' => $populer,
    ]);
})->setName('detail');

$app->get('/kontak', function ($request, $response) {
    return $this->view->render($response, 'view/kontak.html', [

    ]);
})->setName('kontak');

$app->get('/tentang', function ($request, $response) {
    return $this->view->render($response, 'view/tentang.html', [

    ]);
})->setName('tentang');

$app->get('/site/session', function ($request, $response) {
    if (isset($_SESSION['user']['id'])) {
        return successResponse($response, $_SESSION);
    }
    return unprocessResponse($response, ['undefined']);
});

$app->post('/site/login', function ($request, $response) {
    $params = $request->getParams();

    $sql = $this->db;

    $username = isset($params['username']) ? $params['username'] : '';
    $password = isset($params['password']) ? $params['password'] : '';

    $model = $sql->select("*")
        ->from("user")
        ->where("username", "=", $username)
        ->andWhere("password", "=", sha1($password))
        ->find();

    if (isset($model->id)) {
        $_SESSION['user']['id']       = $model->id;
        $_SESSION['user']['username'] = $model->username;
        $_SESSION['user']['nama']     = $model->nama;
        $_SESSION['user']['level']    = $model->level;

        return successResponse($response, $_SESSION);
    }
    return unprocessResponse($response, ['Authentication Systems gagal, username atau password Anda salah.']);
});

$app->get('/site/logout', function () {
    session_destroy();
});

$app->post('/site/upload', function ($request, $response) {
    $files   = $request->getUploadedFiles();
    $newfile = $files['upload'];

    if (file_exists("file/" . $newfile->getClientFilename())) {
        echo $newfile->getClientFilename() . " already exists please choose another image.";
    } else {
        $path           = getenv("IMG_PATH") . 'article/';
        $uploadFileName = date("dYh") . urlParsing($newfile->getClientFilename());
        $upload         = $newfile->moveTo($path . $uploadFileName);

        $url = getenv("SITE_URL") . $path . $uploadFileName;

        // Required: anonymous function reference number as explained above.
        $funcNum = $_GET['CKEditorFuncNum'];
        // Optional: instance name (might be used to load a specific configuration file or anything else).
        $CKEditor = $_GET['CKEditor'];
        // Optional: might be used to provide localized messages.
        $langCode = $_GET['langCode'];

        echo "<script type='text/javascript'> window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '');</script>";
    }
});
