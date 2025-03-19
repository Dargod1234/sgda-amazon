<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Cita Cancelada</title>
</head>
<body>
    <h2>Cita Cancelada - {{ $client->business_name }}</h2>
    
    <p>Hola {{ $client->legal_representative_name }},</p>
    
    <p>La siguiente cita ha sido cancelada:</p>
    
    <ul>
        <li><strong>Título:</strong> {{ $appointment->title }}</li>
        <li><strong>Fecha original:</strong> {{ $appointment->start->format('d-m-Y H:i') }}</li>
        <li><strong>Responsable:</strong> {{ $user->name }}</li>
    </ul>
    
    <p>Si necesitas reprogramar, por favor contáctanos.</p>
    
    @include('emails.partials.footer')
</body>
</html>