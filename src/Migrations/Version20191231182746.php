<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191231182746 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, post_id INT DEFAULT NULL, content LONGTEXT NOT NULL, parent_id INT DEFAULT 0 NOT NULL, author VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, email VARCHAR(255) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, INDEX IDX_9474526C4B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE picture (id INT AUTO_INCREMENT NOT NULL, post_id INT NOT NULL, filename VARCHAR(255) NOT NULL, INDEX IDX_16DB4F894B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, online TINYINT(1) DEFAULT \'0\' NOT NULL, views INT DEFAULT 0 NOT NULL, score INT DEFAULT 0 NOT NULL, INDEX IDX_5A8A6C8DF675F31B (author_id), INDEX IDX_5A8A6C8D12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pub (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, smallfile VARCHAR(255) DEFAULT NULL, largefile VARCHAR(255) DEFAULT NULL, promo TINYINT(1) DEFAULT \'0\' NOT NULL, updated_at DATETIME NOT NULL, link VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_post (tag_id INT NOT NULL, post_id INT NOT NULL, INDEX IDX_B485D33BBAD26311 (tag_id), INDEX IDX_B485D33B4B89032C (post_id), PRIMARY KEY(tag_id, post_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, roles JSON NOT NULL, pseudo VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, password_reset VARCHAR(255) DEFAULT NULL, password_reset_at DATETIME DEFAULT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F894B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tag_post ADD CONSTRAINT FK_B485D33BBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_post ADD CONSTRAINT FK_B485D33B4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D12469DE2');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C');
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F894B89032C');
        $this->addSql('ALTER TABLE tag_post DROP FOREIGN KEY FK_B485D33B4B89032C');
        $this->addSql('ALTER TABLE tag_post DROP FOREIGN KEY FK_B485D33BBAD26311');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DF675F31B');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE picture');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE pub');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_post');
        $this->addSql('DROP TABLE user');
    }
}
