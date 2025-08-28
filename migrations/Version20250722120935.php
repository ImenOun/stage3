<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250722120935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_permissions (user_id INT NOT NULL, permission_id INT NOT NULL, INDEX IDX_84F605FAA76ED395 (user_id), INDEX IDX_84F605FAFED90CCA (permission_id), PRIMARY KEY(user_id, permission_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_permissions ADD CONSTRAINT FK_84F605FAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE user_permissions ADD CONSTRAINT FK_84F605FAFED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id)');
        $this->addSql('ALTER TABLE demande MODIFY idDem INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON demande');
        $this->addSql('ALTER TABLE demande CHANGE idDem id_dem INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE demande ADD PRIMARY KEY (id_dem)');
        $this->addSql('ALTER TABLE demande RENAME INDEX employe_id TO IDX_2694D7A51B65292');
        $this->addSql('ALTER TABLE historique_demande MODIFY idHisto INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON historique_demande');
        $this->addSql('ALTER TABLE historique_demande CHANGE commentaire commentaire LONGTEXT DEFAULT NULL, CHANGE idHisto id_histo INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE historique_demande ADD PRIMARY KEY (id_histo)');
        $this->addSql('ALTER TABLE historique_demande RENAME INDEX demande_id TO IDX_448088DA80E95E18');
        $this->addSql('ALTER TABLE historique_demande RENAME INDEX acteur_id TO IDX_448088DADA6F574A');
        $this->addSql('ALTER TABLE permission CHANGE name name VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE permission RENAME INDEX name TO UNIQ_E04992AA5E237E06');
        $this->addSql('DROP INDEX email ON users');
        $this->addSql('ALTER TABLE users CHANGE email email VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_permissions DROP FOREIGN KEY FK_84F605FAA76ED395');
        $this->addSql('ALTER TABLE user_permissions DROP FOREIGN KEY FK_84F605FAFED90CCA');
        $this->addSql('DROP TABLE user_permissions');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE demande MODIFY id_dem INT NOT NULL');
       $this->addSql('DROP INDEX `PRIMARY` ON demande');
        $this->addSql('ALTER TABLE demande CHANGE id_dem idDem INT AUTO_INCREMENT NOT NULL');
       $this->addSql('ALTER TABLE demande ADD PRIMARY KEY (idDem)');
        $this->addSql('ALTER TABLE demande RENAME INDEX idx_2694d7a51b65292 TO employe_id');
        $this->addSql('ALTER TABLE historique_demande MODIFY id_histo INT NOT NULL');
        $this->addSql('DROP INDEX `PRIMARY` ON historique_demande');
        $this->addSql('ALTER TABLE historique_demande CHANGE commentaire commentaire TEXT DEFAULT NULL, CHANGE id_histo idHisto INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE historique_demande ADD PRIMARY KEY (idHisto)');
        $this->addSql('ALTER TABLE historique_demande RENAME INDEX idx_448088dada6f574a TO acteur_id');
        $this->addSql('ALTER TABLE historique_demande RENAME INDEX idx_448088da80e95e18 TO demande_id');
        $this->addSql('ALTER TABLE permission CHANGE name name VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE permission RENAME INDEX uniq_e04992aa5e237e06 TO name');
        $this->addSql('ALTER TABLE users CHANGE email email VARCHAR(180) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX email ON users (email)');
    }
}
