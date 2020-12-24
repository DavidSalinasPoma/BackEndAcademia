<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensaje nuevo</title>
</head>

<body>
    <p>Nuevo mensaje de:
    <pre>{{ $mensaje['nombres'] }} - {{ $mensaje['correo'] }}</pre>
    </p>
    <p>Celular:
    <pre>{{ $mensaje['celular']}}</pre>
    </p>
    <p>Asunto:
    <pre>{{ $asunto }}</pre>
    </p>


</body>

</html>