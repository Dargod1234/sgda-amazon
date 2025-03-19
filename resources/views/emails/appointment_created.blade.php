<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Notificación de Cita</title>
</head>
<body>
    <h2>¡Hola {{ $client->legal_representative_name }}!</h2>
    <p>Se ha agendado una nueva cita para tu empresa <strong>{{ $client->business_name }}</strong>.</p>
    
    <h3>Detalles de la cita:</h3>
    <ul>
        <li><strong>Título:</strong> {{ $appointment->title }}</li>
        <li><strong>Descripción:</strong> {{ $appointment->description }}</li>
        <li><strong>Fecha/Hora Inicio:</strong> {{ $appointment->start->format('d-m-Y H:i') }}</li>
        <li><strong>Fecha/Hora Fin:</strong> {{ $appointment->end->format('d-m-Y H:i') }}</li>
        <li><strong>Responsable:</strong> {{ $user->name }}</li>
    </ul>
    
    @include('emails.partials.footer')
</body>
</html>