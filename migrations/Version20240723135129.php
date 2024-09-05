<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240723135129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE calendar ADD status VARCHAR(20) DEFAULT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE background_color background_color VARCHAR(7) NOT NULL, CHANGE border_color border_color VARCHAR(7) NOT NULL, CHANGE text_color text_color VARCHAR(7) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE calendar DROP status, CHANGE user_id user_id INT DEFAULT NULL, CHANGE background_color background_color VARCHAR(7) DEFAULT NULL, CHANGE border_color border_color VARCHAR(7) DEFAULT NULL, CHANGE text_color text_color VARCHAR(7) DEFAULT NULL');
    }
}