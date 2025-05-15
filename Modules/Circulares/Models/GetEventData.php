<?php
    function getEventsData ($connect) {
        // Consulta para obtener los eventos desde la tabla circulares
        $query = "SELECT id_file, titulo, fecha_inicio_actividad, fecha_fin_actividad, rama, ubicacion FROM circulares WHERE eliminado = 0 AND enviado = 1"; //TODO ENVIADO AQUI enviado = 1
        $stmt = $connect->prepare($query);
        $stmt->execute();
        
        $events = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Asegúrate de que la fecha esté en el formato adecuado para FullCalendar
            $events[] = [
                'id' => $row['id_file'],
                'title' => $row['titulo'],
                'start' => date('Y-m-d', strtotime($row['fecha_inicio_actividad'])), // Correcto formato ISO
                'end' => date('Y-m-d', strtotime($row['fecha_fin_actividad'] . ' +1 day')), // Sumar 1 día para allDay
                'location' => $row['ubicacion'],
                'category' => $row['rama'],
                'allDay' => true
            ];

        }

        // Devolver los eventos en formato JSON
        header('Content-Type: application/json');
        echo json_encode($events);
    }