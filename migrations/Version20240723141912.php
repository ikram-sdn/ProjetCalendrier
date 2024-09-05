<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240723141912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }



    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calendar ADD status VARCHAR(20) DEFAULT \'en cours\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calendar ADD status VARCHAR(20) DEFAULT \'en cours\'');
    }
}
