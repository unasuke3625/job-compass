<?php

    require_once __DIR__ . '/includes/db.php';
    
    $sql = "
    CREATE TABLE selections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        selection_type VARCHAR(50) NOT NULL,
        deadline DATE DEFAULT NULL,
        event_date DATE DEFAULT NULL,
        start_time TIME DEFAULT NULL,
        location_type VARCHAR(20) DEFAULT NULL,
        location VARCHAR(255) DEFAULT NULL,
        status VARCHAR(30) NOT NULL DEFAULT '予定',
        memo TEXT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL
            DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id)
            REFERENCES companies(id)
            ON DELETE CASCADE
    )
    ";
    
    $pdo->exec($sql);
    
    echo "companiesテーブルを作成しました。";

?>