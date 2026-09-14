<?php
class Migration {
    public function __construct(private PDO $pdo)
    {
        
    }

    public function getMigrations():array {
        $sql = "SELECT * FROM `migrations`";
        $sth = $this->pdo->query($sql);
        return $sth->fetchAll(PDO::FETCH_COLUMN);
    }

    public function saveMigration(string $file) {
        $sql = "INSERT INTO `migrations` VALUES(:file)";
        $sth = $this->pdo->prepare($sql);
        $sth->execute(["file" => $file]);
        return $sth->rowCount();
    }

    public function migrate() {
        $currMigrations = $this->getMigrations();

        $result = scandir(__DIR__ . "/");
        $files = array_diff($result, array('.', '..', 'migration.php'));

        $needMigrations = array_diff($files, $currMigrations);

        foreach($needMigrations as $migration) {
            $sql = file_get_contents(__DIR__ . "/" . $migration);
            try {
                $sth = $this->pdo->exec($sql);
            } catch (PDOException $e) {
                echo "Error occurred while executing migration: " . $e->getMessage();
            }
            
            $this->saveMigration($migration);
        }
    }
}