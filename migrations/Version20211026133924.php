<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211026133924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book ADD category_id INT NOT NULL, ADD game_id INT NOT NULL');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A33112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A331E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CBE5A33112469DE2 ON book (category_id)');
        $this->addSql('CREATE INDEX IDX_CBE5A331E48FD905 ON book (game_id)');
        $this->addSql('ALTER TABLE commentary ADD book_id INT NOT NULL, ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE commentary ADD CONSTRAINT FK_1CAC12CA16A2B381 FOREIGN KEY (book_id) REFERENCES book (id)');
        $this->addSql('ALTER TABLE commentary ADD CONSTRAINT FK_1CAC12CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_1CAC12CA16A2B381 ON commentary (book_id)');
        $this->addSql('CREATE INDEX IDX_1CAC12CAA76ED395 ON commentary (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A33112469DE2');
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A331E48FD905');
        $this->addSql('DROP INDEX UNIQ_CBE5A33112469DE2 ON book');
        $this->addSql('DROP INDEX IDX_CBE5A331E48FD905 ON book');
        $this->addSql('ALTER TABLE book DROP category_id, DROP game_id');
        $this->addSql('ALTER TABLE commentary DROP FOREIGN KEY FK_1CAC12CA16A2B381');
        $this->addSql('ALTER TABLE commentary DROP FOREIGN KEY FK_1CAC12CAA76ED395');
        $this->addSql('DROP INDEX IDX_1CAC12CA16A2B381 ON commentary');
        $this->addSql('DROP INDEX IDX_1CAC12CAA76ED395 ON commentary');
        $this->addSql('ALTER TABLE commentary DROP book_id, DROP user_id');
    }
}
