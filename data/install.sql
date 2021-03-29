SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Basic Permissions
--
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES
('index', 'OnePlace\\Testing\\Controller\\BackendController', 'Overview', 'Testing', '/testing', 1, 0),
('view', 'OnePlace\\Testing\\Controller\\BackendController', 'View Test', '', '', 0, 0),
('start', 'OnePlace\\Testing\\Controller\\BackendController', 'Start Test', '', '', 0, 0);


--
-- Nav Icon
--
INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES ('testing-icon', 'fab fa-android');

COMMIT;
