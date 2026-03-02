<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302091515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE coupon (id SERIAL PRIMARY KEY, code VARCHAR(50) NOT NULL, type VARCHAR(20) NOT NULL, value INTEGER NOT NULL, active BOOLEAN NOT NULL, starts_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ends_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, usage_limit INTEGER DEFAULT NULL, used_count INTEGER NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64BF3F0277153098 ON coupon (code)');
        $this->addSql('CREATE TABLE review (id SERIAL PRIMARY KEY, rating INTEGER NOT NULL, comment TEXT NOT NULL, is_approved BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, product_id INTEGER NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_794381C64584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_794381C64584665A ON review (product_id)');
        $this->addSql('CREATE INDEX IDX_794381C6A76ED395 ON review (user_id)');
        $this->addSql('ALTER TABLE customer_order ADD COLUMN coupon_code VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD COLUMN subtotal INTEGER NOT NULL');
        $this->addSql('ALTER TABLE customer_order ADD COLUMN shipping_method VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE customer_order ADD COLUMN shipping_fee INTEGER NOT NULL');
        $this->addSql('ALTER TABLE customer_order ADD COLUMN discount_amount INTEGER NOT NULL');
        $this->addSql('ALTER TABLE customer_order ADD COLUMN payment_method VARCHAR(30) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE coupon');
        $this->addSql('DROP TABLE review');
        $this->addSql('CREATE TEMPORARY TABLE __temp__customer_order AS SELECT id, customer_name, email, phone, address_line1, address_line2, city, postal_code, country, status, total_amount, created_at, updated_at, user_id FROM customer_order');
        $this->addSql('DROP TABLE customer_order');
        $this->addSql('CREATE TABLE customer_order (id SERIAL PRIMARY KEY, customer_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(30) DEFAULT NULL, address_line1 VARCHAR(255) NOT NULL, address_line2 VARCHAR(255) DEFAULT NULL, city VARCHAR(100) NOT NULL, postal_code VARCHAR(30) DEFAULT NULL, country VARCHAR(80) NOT NULL, status VARCHAR(30) NOT NULL, total_amount INTEGER NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_id INTEGER DEFAULT NULL, CONSTRAINT FK_3B1CE6A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO customer_order (id, customer_name, email, phone, address_line1, address_line2, city, postal_code, country, status, total_amount, created_at, updated_at, user_id) SELECT id, customer_name, email, phone, address_line1, address_line2, city, postal_code, country, status, total_amount, created_at, updated_at, user_id FROM __temp__customer_order');
        $this->addSql('DROP TABLE __temp__customer_order');
        $this->addSql('CREATE INDEX IDX_3B1CE6A3A76ED395 ON customer_order (user_id)');
    }
}
