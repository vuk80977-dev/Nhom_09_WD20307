<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title><?= isset($title) ? $title . ' | Tour' : 'Tour' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background:#f5f6fa; }
        .auth-card { max-width: 520px; }
    </style>
</head>
<body>

<div class="container py-5">
    <?= $content ?? '' ?>
</div>

</body>
</html>
