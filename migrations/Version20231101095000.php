<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231101095000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add User <-> Project relation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD user_project_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN project.user_project_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEB10AD970 FOREIGN KEY (user_project_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2FB3D0EEB10AD970 ON project (user_project_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EEB10AD970');
        $this->addSql('DROP INDEX IDX_2FB3D0EEB10AD970');
        $this->addSql('ALTER TABLE project DROP user_project_id');
    }
}
