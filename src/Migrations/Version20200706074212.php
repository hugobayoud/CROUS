<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200706074212 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE couple (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, service_id INT NOT NULL, INDEX IDX_D840B549A76ED395 (user_id), INDEX IDX_D840B549ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE droit_effectif (id INT AUTO_INCREMENT NOT NULL, application_id INT NOT NULL, couple_id INT NOT NULL, date_deb DATETIME NOT NULL, date_fin DATETIME NOT NULL, INDEX IDX_28E30F233E030ACD (application_id), INDEX IDX_28E30F23F66468CA (couple_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE couple ADD CONSTRAINT FK_D840B549A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE couple ADD CONSTRAINT FK_D840B549ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE droit_effectif ADD CONSTRAINT FK_28E30F233E030ACD FOREIGN KEY (application_id) REFERENCES application (id)');
        $this->addSql('ALTER TABLE droit_effectif ADD CONSTRAINT FK_28E30F23F66468CA FOREIGN KEY (couple_id) REFERENCES couple (id)');
        $this->addSql('ALTER TABLE application CHANGE libelle libelle VARCHAR(50) DEFAULT NULL, CHANGE type type VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE service CHANGE libelle_court libelle_court VARCHAR(20) DEFAULT NULL, CHANGE libelle_long libelle_long VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE activation_token activation_token VARCHAR(50) DEFAULT NULL, CHANGE reset_token reset_token VARCHAR(50) DEFAULT NULL, CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE droit_effectif DROP FOREIGN KEY FK_28E30F23F66468CA');
        $this->addSql('DROP TABLE couple');
        $this->addSql('DROP TABLE droit_effectif');
        $this->addSql('ALTER TABLE application CHANGE libelle libelle VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE service CHANGE libelle_court libelle_court VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE libelle_long libelle_long VARCHAR(80) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user CHANGE activation_token activation_token VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE reset_token reset_token VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
