<?php
function validateLoginForm($data) {
    $errors = [];

    // Validar email
    if (empty($data['email'])) {
        $errors['email'] = "Inserte un correo electronico";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Inserte un correo electronico valido";
    }

    // Validar contraseña
    if (empty($data['password'])) {
        $errors['password'] = "Inserte una contraseña";
    } elseif (strlen($data['password']) < 6) {
        $errors['password'] = "La contraseña debe tener al menos 6 caracteres";
    }

    return $errors;
}

function validateSignInForm($data) {
    $errors = [];

    // Validar nombre
    if (empty($data['nombre'])) {
        $errors['nombre'] = "Rellene este campo";
    } elseif (strlen($data['nombre']) < 2) {
        $errors['nombre'] = "Debe tener al menos 2 caracteres.";
    }

    // Validar apellido
    if (empty($data['apellido'])) {
        $errors['apellido'] = "Rellene este campo";
    } elseif (strlen($data['apellido']) < 2) {
        $errors['apellido'] = "Debe tener al menos 2 caracteres.";
    }

    // Validar email
    if (empty($data['email'])) {
        $errors['email'] = "Inserte un correo electronico";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Inserte un correo electronico valido";
    }

    // Validar teléfono
    if (empty($data['telefono'])) {
        $errors['telefono'] = "Por favor, ingresa tu número de teléfono.";
    } elseif (!ctype_digit($data['telefono']) || strlen($data['telefono']) < 9 || strlen($data['telefono']) > 15) {
        $errors['telefono'] = "El teléfono debe tener entre 9 y 15 dígitos.";
    }

    // Validar contraseñas
    if (empty($data['password1'])) {
        $errors['password1'] = "Inserte una contraseña";
    } elseif (strlen($data['password1']) < 6) {
        $errors['password1'] = "La contraseña debe tener al menos 6 caracteres";
    }

    if (empty($data['password2'])) {
        $errors['password2'] = "Inserte una contraseña";
    } elseif ($data['password1'] !== $data['password2']) {
        $errors['password2'] = "Las contraseñas no son iguales";
    }

    return $errors;
}

function validateLostPasswordForm($data) {
    $errors = [];

    // Validar email
    if (empty($data['email'])) {
        $errors['email'] = "Inserte un correo electronico";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Inserte un correo electronico valido";
    }

    return $errors;
}

// Example usage for login validation
$loginErrors = validateLoginForm($_POST);
if (!empty($loginErrors)) {
    echo json_encode(['error' => true, 'messages' => $loginErrors]);
    exit;
}

// Example usage for logout validation (if needed)
$logoutErrors = validateLogoutForm($_POST);
if (!empty($logoutErrors)) {
    echo json_encode(['error' => true, 'messages' => $logoutErrors]);
    exit;
}

function validateLogoutForm($data) {
    $errors = [];

    // Assuming logout requires user ID validation
    if (empty($data['user_id'])) {
        $errors['user_id'] = "User ID is required for logout";
    }
    return $errors;
}