<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200203082036 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE teacher_subject (teacher_id INT NOT NULL, subject_id INT NOT NULL, INDEX IDX_360CB33B41807E1D (teacher_id), INDEX IDX_360CB33B23EDC87 (subject_id), PRIMARY KEY(teacher_id, subject_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE teacher_subject ADD CONSTRAINT FK_360CB33B41807E1D FOREIGN KEY (teacher_id) REFERENCES teacher (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE teacher_subject ADD CONSTRAINT FK_360CB33B23EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id) ON DELETE CASCADE');
        // $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(180) NOT NULL, CHANGE roles roles JSON NOT NULL, CHANGE password password VARCHAR(255) NOT NULL');
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        // $this->addSql('ALTER TABLE reschedule CHANGE grade_id grade_id INT DEFAULT NULL, CHANGE lesson_id lesson_id INT DEFAULT NULL, CHANGE room_id room_id INT DEFAULT NULL, CHANGE teacher_id teacher_id INT DEFAULT NULL');
        // $this->addSql('ALTER TABLE absence CHANGE teacher_id teacher_id INT DEFAULT NULL');
        // $this->addSql('ALTER TABLE lesson CHANGE room_id room_id INT DEFAULT NULL, CHANGE subject_id subject_id INT DEFAULT NULL, CHANGE grade_id grade_id INT DEFAULT NULL, CHANGE teacher_id teacher_id INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE teacher_subject');
        $this->addSql('ALTER TABLE absence CHANGE teacher_id teacher_id INT NOT NULL');
        $this->addSql('ALTER TABLE lesson CHANGE teacher_id teacher_id INT NOT NULL, CHANGE room_id room_id INT NOT NULL, CHANGE subject_id subject_id INT NOT NULL, CHANGE grade_id grade_id INT NOT NULL');
        $this->addSql('ALTER TABLE reschedule CHANGE grade_id grade_id INT NOT NULL, CHANGE lesson_id lesson_id INT DEFAULT NULL, CHANGE room_id room_id INT DEFAULT NULL, CHANGE teacher_id teacher_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_8D93D649F85E0677 ON user');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_czech_ci`, CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, CHANGE password password TEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_czech_ci`');
    }
}
