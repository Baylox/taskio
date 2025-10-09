<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251008132620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE board_invitation (id INT AUTO_INCREMENT NOT NULL, board_id INT NOT NULL, invited_by_id INT NOT NULL, email VARCHAR(255) NOT NULL, token VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_accepted TINYINT(1) NOT NULL, accepted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_5D13B51E5F37A13B (token), INDEX IDX_5D13B51EE7EC5785 (board_id), INDEX IDX_5D13B51EA7B4A7E3 (invited_by_id), INDEX idx_token (token), INDEX idx_email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE board_invitation ADD CONSTRAINT FK_5D13B51EE7EC5785 FOREIGN KEY (board_id) REFERENCES board (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE board_invitation ADD CONSTRAINT FK_5D13B51EA7B4A7E3 FOREIGN KEY (invited_by_id) REFERENCES account (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board_invitation DROP FOREIGN KEY FK_5D13B51EE7EC5785');
        $this->addSql('ALTER TABLE board_invitation DROP FOREIGN KEY FK_5D13B51EA7B4A7E3');
        $this->addSql('DROP TABLE board_invitation');
    }
}
