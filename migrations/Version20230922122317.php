<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230922122317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set Project and Category names uniques';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category ALTER name SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C15E237E06 ON category (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2FB3D0EE5E237E06 ON project (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_64C19C15E237E06');
        $this->addSql('ALTER TABLE category ALTER name DROP NOT NULL');
        $this->addSql('DROP INDEX UNIQ_2FB3D0EE5E237E06');
    }
}
