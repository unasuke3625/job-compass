<?php

    require_once __DIR__ . '/includes/db.php';
    
    $sql = "
    CREATE TABLE IF NOT EXISTS companies (
        id INT AUTO_INCREMENT PRIMARY KEY,
    
        company_name VARCHAR(255) NOT NULL,
        industry VARCHAR(100),
        job_type VARCHAR(100),
        application_route VARCHAR(100),
        service_name VARCHAR(100),
    
        login_url VARCHAR(1000),
        login_email VARCHAR(255),
        login_id VARCHAR(255),
        password_management VARCHAR(255) COMMENT '管理方法のみ。実パスワード保存禁止。アプリ側名: credential_note',
    
        interest_level INT,
        status VARCHAR(50),
    
        memo TEXT,
    
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP
    )
    ";
    
    $pdo->exec($sql);
    
    echo "companiesテーブルを作成しました。";

?>
