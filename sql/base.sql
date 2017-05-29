-- -----------------------------------------------------
-- Schema AppUpdater
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `AppUpdater` DEFAULT CHARACTER SET utf8 ;
USE `AppUpdater` ;

-- -----------------------------------------------------
-- Table `AppUpdater`.`Apps`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `AppUpdater`.`Apps` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL,
  `baseUrl` TEXT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `AppUpdater`.`Platforms`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `AppUpdater`.`Platforms` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `AppUpdater`.`Versions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `AppUpdater`.`Versions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `idApp` INT UNSIGNED NOT NULL,
  `idPlatform` INT UNSIGNED NOT NULL,
  `version` VARCHAR(255) NULL,
  `checksum` TEXT NULL,
  `creationTime` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Versions_Platforms1_idx` (`idPlatform` ASC),
  INDEX `fk_Versions_App1_idx` (`idApp` ASC),
  CONSTRAINT `fk_Versions_Platforms1`
    FOREIGN KEY (`idPlatform`)
    REFERENCES `AppUpdater`.`Platforms` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Versions_App1`
    FOREIGN KEY (`idApp`)
    REFERENCES `AppUpdater`.`Apps` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `AppUpdater`.`AppSupportPlatform`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `AppUpdater`.`AppSupportPlatform` (
  `idPlatform` INT UNSIGNED NOT NULL,
  `appId` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`idPlatform`, `appId`),
  INDEX `fk_Platforms_has_App_App1_idx` (`appId` ASC),
  INDEX `fk_Platforms_has_App_Platforms_idx` (`idPlatform` ASC),
  CONSTRAINT `fk_Platforms_has_App_Platforms`
    FOREIGN KEY (`idPlatform`)
    REFERENCES `AppUpdater`.`Platforms` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Platforms_has_App_App1`
    FOREIGN KEY (`appId`)
    REFERENCES `AppUpdater`.`Apps` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `AppUpdater`.`Users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `AppUpdater`.`Users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` TEXT NULL,
  `email` TEXT NULL,
  `password` TEXT NULL,
  `creationTime` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `AppUpdater`.`UsersHasAccesToApp`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `AppUpdater`.`UsersHasAccesToApp` (
  `Users_id` INT UNSIGNED NOT NULL,
  `App_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`Users_id`, `App_id`),
  INDEX `fk_Users_has_App_App1_idx` (`App_id` ASC),
  INDEX `fk_Users_has_App_Users1_idx` (`Users_id` ASC),
  CONSTRAINT `fk_Users_has_App_Users1`
    FOREIGN KEY (`Users_id`)
    REFERENCES `AppUpdater`.`Users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Users_has_App_App1`
    FOREIGN KEY (`App_id`)
    REFERENCES `AppUpdater`.`Apps` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;