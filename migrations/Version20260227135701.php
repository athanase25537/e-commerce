<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227135701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD COLUMN stock INTEGER NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__product AS SELECT id, name, description, price, image FROM product');
        $this->addSql('DROP TABLE product');
        $this->addSql('CREATE TABLE product (id SERIAL PRIMARY KEY, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, price INTEGER NOT NULL, image VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO product (id, name, description, price, image) SELECT id, name, description, price, image FROM __temp__product');
        $this->addSql('DROP TABLE __temp__product');
    }
}
