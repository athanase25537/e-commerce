<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225111011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(\"ALTER TABLE app_user ADD COLUMN firstname VARCHAR(255) NOT NULL DEFAULT ''\");
        $this->addSql(\"ALTER TABLE app_user ALTER COLUMN firstname DROP DEFAULT\");
        $this->addSql(\"ALTER TABLE app_user ADD COLUMN lastname VARCHAR(255) NOT NULL DEFAULT ''\");
        $this->addSql(\"ALTER TABLE app_user ALTER COLUMN lastname DROP DEFAULT\");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_user AS SELECT id, email, roles, password FROM app_user');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('CREATE TABLE app_user (id SERIAL PRIMARY KEY, email VARCHAR(180) NOT NULL, roles TEXT NOT NULL, password VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO app_user (id, email, roles, password) SELECT id, email, roles, password FROM __temp__app_user');
        $this->addSql('DROP TABLE __temp__app_user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON app_user (email)');
    }
}
