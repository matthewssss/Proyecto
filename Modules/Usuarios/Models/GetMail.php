<?php 

    function getMail ($connect, $mail) {    
        // Prepare the SQL statement with placeholders
        $sql = "SELECT * FROM usuarios WHERE correo = :mail AND eliminado = 0";
        $stmt = $connect->prepare($sql);
    
        // Bind parameters to the placeholders
        $stmt->bindParam(':mail', $mail, PDO::PARAM_STR);
    
        // Execute the statement
        $stmt->execute();
    
        // Fetch all results and return
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }