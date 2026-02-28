<?php
require_once 'db_connect.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS general_education (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        duration VARCHAR(50),
        thumbnail_url VARCHAR(255),
        video_url VARCHAR(255),
        category VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table created successfully\n";

    // Insert initial data if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM general_education");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO general_education (title, description, duration, thumbnail_url, video_url, category) VALUES (?, ?, ?, ?, ?, ?)");
        
        $data = [
            ['Dental Implant Procedure | Medical Animation', 'Gentle step-by-step 3D animation showing how a dental implant is placed. Explains why implants are better than bridges. Very calm and easy to understand.', '5:41', 'uploads/educational/thumbnail.jpg', 'uploads/educational/video.mp4', 'Implant Surgery'],
            ['Dental Implants (3D Animation)', 'Short and clear 3D explanation of what a dental implant is, how it fuses with bone, and how it functions like a natural tooth root.', '1:29', 'uploads/educational/thumbnail.jpg', 'uploads/educational/video.mp4', 'Implant Surgery'],
            ['Cleaning Your Dental Implant', 'Simple step-by-step guide on how to clean around your implant using super-floss. Essential daily hygiene for long-lasting implants.', '2:06', 'uploads/educational/thumbnail.jpg', 'uploads/educational/video.mp4', 'Hygiene'],
            ['Dos and Don\'ts After Dental Implant Surgery', '10 easy rules for the first days after surgery — includes gentle brushing, rinsing, ice cream tip, and when to start normal hygiene.', '2:49', 'uploads/educational/thumbnail.jpg', 'uploads/educational/video.mp4', 'Hygiene'],
            ['Understanding Root Canals', 'Deep dive into the root canal procedure, why it is necessary, and what to expect during recovery.', '5:41', 'uploads/educational/thumbnail.jpg', 'uploads/educational/video.mp4', 'Endodontics'],
            ['Proper Brushing Technique', 'Expert tips from hygienist Sarah on how to brush correctly to maintain optimal oral health.', '3:20', 'uploads/educational/thumbnail.jpg', 'uploads/educational/video.mp4', 'Hygiene'],
            ['Post-Op Recovery Tips', 'Essential advice from Dr. Sandeep on how to manage pain and swelling after dental surgery.', '4:15', 'uploads/educational/thumbnail.jpg', 'uploads/educational/video.mp4', 'Recovery']
        ];

        foreach ($data as $row) {
            $stmt->execute($row);
        }
        echo "Initial data inserted successfully\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
