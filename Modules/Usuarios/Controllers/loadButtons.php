<?php
    /*  
        ------------------ PERMISOS ------------------
        ["ver_info", "ver_datos_chavales", "gestionar_documentacion", "ver_unidad", "administrar_todo"] 

    */
    
    function loadButtons () {    
        // Definir los elementos del menú y los permisos requeridos
        $menuItems = [
            'profile' => [
                'label' => 'Perfil',
                'icon' => 'ic:baseline-person',
                'alwaysVisible' => true
            ],
            'asociados' => [
                'label' => 'Asociados',
                'icon' => 'ic:baseline-supervisor-account',
                'alwaysVisible' => true
            ],
            'kraal' => [
                'label' => 'Kraal',
                'icon' => 'ic:baseline-groups',
                'alwaysVisible' => true
            ],
            'circulares' => [
                'label' => 'Circulares',
                'icon' => 'ic:baseline-file-copy',
                'permission' => 'gestionar_documentacion'
            ],
            'usuarios' => [
                'label' => 'Usuarios',
                'icon' => 'ic:round-person-search',
                'permission' => 'administrar_todo'
            ]
        ];
        
        // Variable para almacenar los elementos del menú
        $menuHtml = "";

        $user = unserialize($_SESSION['user']);
        // Generar los elementos del menú según los permisos
        foreach ($menuItems as $id => $item) {
            if (isset($item['alwaysVisible']) || $user->tienePermiso($item['permission'] ?? '')) {
                $menuHtml .= "<li id=\"$id\" class=\"flex cursor-pointer items-center text-xl px-4 py-3 rounded-lg border border-gray-600 bg-gray-800 text-white hover:bg-gray-700 hover:text-gray-200 transition-all duration-300 ease-in-out\">";
                $menuHtml .= "<span class=\"iconify text-gray-300 me-3 text-3xl\" data-icon=\"{$item['icon']}\"></span>";
                $menuHtml .= "{$item['label']}";
                $menuHtml .= "</li>";
            }
        }
        
        // Devolver el menú en formato JSON
        echo json_encode(["menu" => $menuHtml]);
    }
    