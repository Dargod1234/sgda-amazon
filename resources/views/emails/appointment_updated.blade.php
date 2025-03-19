<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Cita Actualizada</title>
</head>
<body>
    <h2>Cita Modificada - {{ $client->business_name }}</h2>
    
    <p>Hola {{ $client->legal_representative_name }},</p>
    
    <p>Se han realizado cambios en tu cita:</p>
    
    <div style="border: 1px solid #e2e8f0; padding: 1rem; margin: 1rem 0;">
        <h3>Cambios realizados:</h3>
        @if($originalData['start'] != $appointment->start)
        <p><strong>Nueva fecha:</strong> {{ $appointment->start->format('d-m-Y H:i') }}</p>
        @endif
        
        @if($originalData['title'] != $appointment->title)
        <p><strong>Nuevo título:</strong> {{ $appointment->title }}</p>
        @endif
        
        @if($originalData['description'] != $appointment->description)
        <p><strong>Nueva descripción:</strong> {{ $appointment->description }}</p>
        @endif
    </div>

    @include('emails.partials.footer')
</body>
</html>