SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Basic Permissions
--
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES
('index', 'OnePlace\\Android\\Builder\\Controller\\WizardController', 'Overview', 'Android Apps', '/android/builder', 1, 0);

--
-- Nav Icon
--
INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES ('testing-icon', 'fab fa-android');

COMMIT;
