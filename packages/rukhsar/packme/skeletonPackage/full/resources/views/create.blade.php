<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>:PackageName - Create</title>
</head>
<body>
    <h1>Create :PackageName</h1>
    <form action="/:package_name" method="POST">
        @csrf
        <input type="text" name="name" placeholder="Name">
        <button type="submit">Create</button>
    </form>
</body>
</html>
