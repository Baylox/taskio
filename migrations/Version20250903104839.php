<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903104839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(50) NOT NULL, is_verified TINYINT(1) NOT NULL, name VARCHAR(50) NOT NULL, lastname VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_7D3656A4E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE account_board (account_id INT NOT NULL, board_id INT NOT NULL, INDEX IDX_6ED9F2369B6B5FBA (account_id), INDEX IDX_6ED9F236E7EC5785 (board_id), PRIMARY KEY(account_id, board_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE board (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, lane_id INT NOT NULL, title VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(24) NOT NULL, position INT NOT NULL, INDEX IDX_161498D3A128F72F (lane_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lane (id INT AUTO_INCREMENT NOT NULL, board_id INT NOT NULL, title VARCHAR(50) NOT NULL, position INT NOT NULL, INDEX IDX_DF07E54EE7EC5785 (board_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE account_board ADD CONSTRAINT FK_6ED9F2369B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE account_board ADD CONSTRAINT FK_6ED9F236E7EC5785 FOREIGN KEY (board_id) REFERENCES board (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D3A128F72F FOREIGN KEY (lane_id) REFERENCES lane (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lane ADD CONSTRAINT FK_DF07E54EE7EC5785 FOREIGN KEY (board_id) REFERENCES board (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account_board DROP FOREIGN KEY FK_6ED9F2369B6B5FBA');
        $this->addSql('ALTER TABLE account_board DROP FOREIGN KEY FK_6ED9F236E7EC5785');
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D3A128F72F');
        $this->addSql('ALTER TABLE lane DROP FOREIGN KEY FK_DF07E54EE7EC5785');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE account_board');
        $this->addSql('DROP TABLE board');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE lane');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
