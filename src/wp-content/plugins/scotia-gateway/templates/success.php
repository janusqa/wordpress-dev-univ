<?php ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success</title>
</head>

<body>
    <?php if (isset($_POST['data'])) : ?>
        <p>Data: <?php echo esc_html($_POST['data']); ?></p>
    <?php endif; ?>
</body>

</html>