<?php
namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200701060504 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE application_demande (id INT AUTO_INCREMENT NOT NULL, application_id INT NOT NULL, demande_id INT NOT NULL, a_supprimer TINYINT(1) NOT NULL, date_deb DATETIME NOT NULL, date_fin DATETIME NOT NULL, INDEX IDX_CD2AD2963E030ACD (application_id), INDEX IDX_CD2AD29680E95E18 (demande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE application_demande ADD CONSTRAINT FK_CD2AD2963E030ACD FOREIGN KEY (application_id) REFERENCES application (id)');
        $this->addSql('ALTER TABLE application_demande ADD CONSTRAINT FK_CD2AD29680E95E18 FOREIGN KEY (demande_id) REFERENCES demande (id)');
       // $this->addSql('ALTER TABLE application CHANGE libelle libelle VARCHAR(50) DEFAULT NULL, CHANGE type type VARCHAR(2) DEFAULT NULL');
        //$this->addSql('ALTER TABLE service CHANGE libelle_court libelle_court VARCHAR(20) DEFAULT NULL, CHANGE libelle_long libelle_long VARCHAR(80) DEFAULT NULL');
        //$this->addSql('ALTER TABLE user CHANGE activation_token activation_token VARCHAR(50) DEFAULT NULL, CHANGE reset_token reset_token VARCHAR(50) DEFAULT NULL, CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE application_demande');
        //$this->addSql('ALTER TABLE application CHANGE libelle libelle VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        //this->addSql('ALTER TABLE service CHANGE libelle_court libelle_court VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE libelle_long libelle_long VARCHAR(80) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        //$this->addSql('ALTER TABLE user CHANGE activation_token activation_token VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE reset_token reset_token VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
