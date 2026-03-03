<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store product images in the database as binary data.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ALTER COLUMN image DROP DEFAULT');
        $this->addSql('ALTER TABLE product ALTER COLUMN image TYPE BYTEA USING image::bytea');
        $this->addSql('ALTER TABLE product ADD image_mime_type VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE product ALTER COLUMN image TYPE VARCHAR(255) USING SUBSTRING(encode(image, 'escape') FROM 1 FOR 255)");
        $this->addSql('ALTER TABLE product DROP COLUMN image_mime_type');
    }
}
