<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230906163121 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add Project slug column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD slug VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project DROP slug');
    }
}
