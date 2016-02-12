-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema db_userservice
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `db_userservice` ;

-- -----------------------------------------------------
-- Schema db_userservice
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `db_userservice` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `db_userservice` ;

-- -----------------------------------------------------
-- Table `db_userservice`.`us_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `db_userservice`.`us_user` ;

CREATE TABLE IF NOT EXISTS `db_userservice`.`us_user` (
  `id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '',
  `hash_id` VARCHAR(50) NOT NULL COMMENT '',
  `first_name` VARCHAR(50) NOT NULL COMMENT '',
  `last_name` VARCHAR(50) NOT NULL COMMENT '',
  `email` VARCHAR(50) NOT NULL COMMENT '',
  `username` VARCHAR(45) NOT NULL COMMENT '',
  `password` VARCHAR(100) NOT NULL COMMENT '',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '',
  `created_on` DATETIME NOT NULL COMMENT '',
  `updated_on` DATETIME NOT NULL COMMENT '',
  PRIMARY KEY (`id`, `email`)  COMMENT 'Phase 1',
  UNIQUE INDEX `email_UNIQUE` (`email` ASC)  COMMENT '')
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_userservice`.`us_address`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `db_userservice`.`us_address` ;

CREATE TABLE IF NOT EXISTS `db_userservice`.`us_address` (
  `id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '',
  `user_id` INT NOT NULL COMMENT '',
  `address1` VARCHAR(90) NOT NULL COMMENT '',
  `address2` VARCHAR(90) NULL COMMENT '',
  `zip` VARCHAR(15) NOT NULL COMMENT '',
  `country` VARCHAR(80) NOT NULL COMMENT '',
  `mobile_phone` VARCHAR(20) NULL COMMENT '',
  `home_phone` VARCHAR(20) NULL COMMENT '',
  `created_on` DATE NOT NULL COMMENT '',
  `updated_on` DATETIME NOT NULL COMMENT '',
  `us_user_id` INT NOT NULL COMMENT '',
  PRIMARY KEY (`id`, `us_user_id`, `user_id`)  COMMENT 'Phase 1',
  INDEX `fk_us_address_us_user_idx` (`us_user_id` ASC, `user_id` ASC)  COMMENT '')
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;