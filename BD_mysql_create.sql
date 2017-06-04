CREATE TABLE `Perfiles` (
	`id_perfiles` INT NOT NULL AUTO_INCREMENT,
	`descripcion` varchar(50) NOT NULL UNIQUE,
	PRIMARY KEY (`id_perfiles`)
);


CREATE TABLE `Usuarios` (
	`id_usuarios` INT NOT NULL AUTO_INCREMENT,
	`usuario` varchar(20) NOT NULL UNIQUE,
	`clave` varchar(20) NOT NULL,
	`nombre` varchar(100) NOT NULL,
	`apellidos` varchar(100) NOT NULL,
	`email` varchar(100) NOT NULL,
	`telefono` INT(9),
	`validado` BOOLEAN DEFAULT false,
	`id_perfiles` INT NOT NULL,
	`dni` varchar(20) NOT NULL UNIQUE,
	PRIMARY KEY (`id_usuarios`)
);


CREATE TABLE `Propuestas` (
	`id_propuestas` INT NOT NULL AUTO_INCREMENT,
	`id_tipospropuestas` INT NOT NULL,
	`id_usuarios` INT NOT NULL,
	`id_ambitospropuestas` INT NOT NULL,
	`descripcion` varchar(5000) NOT NULL,
	`fecha_creacion` DATE NOT NULL,
	`grupal` BOOLEAN NOT NULL DEFAULT false,
	`fecha_cierre` DATE NOT NULL,
	`fecha_propuesta` DATE,
	`n_horasdia` INT(2),
	`fecha_fin` DATE,
	`n_dias` INT,
	`n_horastotal` INT,
	PRIMARY KEY (`id_propuestas`)
);

CREATE TABLE `TiposPropuestas` (
	`id_tipospropuestas` INT NOT NULL AUTO_INCREMENT,
	`descripcion` varchar(50) NOT NULL UNIQUE,
	PRIMARY KEY (`id_tipospropuestas`)
);

CREATE TABLE `Acuerdos` (
	`id_acuerdos` INT NOT NULL AUTO_INCREMENT,
	`id_propuestas` INT NOT NULL,
	`id_usuarios2` INT NOT NULL,
	`id_estadosacuerdos1` INT NOT NULL,
	`id_estadosacuerdos2` INT NOT NULL,
	`n_horastotal` INT NOT NULL DEFAULT 0,
	PRIMARY KEY (`id_acuerdos`)
);

CREATE TABLE `Comentarios` (
	`id_comentarios` INT NOT NULL AUTO_INCREMENT,
	`id_acuerdos` INT NOT NULL,
	`id_usuarios` INT NOT NULL,
	`descripcion` varchar(5000) NOT NULL,
	`fecha_comentario` DATE NOT NULL,
	PRIMARY KEY (`id_comentarios`)
);

CREATE TABLE `EstadosAcuerdos` (
	`id_estadosacuerdos` INT NOT NULL AUTO_INCREMENT,
	`descripcion` varchar(50) NOT NULL UNIQUE,
	PRIMARY KEY (`id_estadosacuerdos`)
);

CREATE TABLE `Eventos` (
	`id_eventos` INT NOT NULL AUTO_INCREMENT,
	`id_usuarios` INT NOT NULL,
	`descripcion` varchar(5000) NOT NULL,
	`fecha_evento` DATE NOT NULL,
	PRIMARY KEY (`id_eventos`)
);

CREATE TABLE `MensajesPropuestas` (
	`id_mensajespropuestas` INT NOT NULL AUTO_INCREMENT,
	`id_propuestas` INT NOT NULL,
	`id_usuarios` INT NOT NULL,
	`mensajes` varchar(5000) NOT NULL,
	`fecha_envio` DATE NOT NULL,
	PRIMARY KEY (`id_mensajespropuestas`)
);

CREATE TABLE `AmbitosPropuestas` (
	`id_ambitospropuestas` INT NOT NULL AUTO_INCREMENT,
	`descripcion` varchar(50) NOT NULL,
	PRIMARY KEY (`id_ambitospropuestas`)
);

ALTER TABLE `Usuarios` ADD CONSTRAINT `Usuarios_fk0` FOREIGN KEY (`id_perfiles`) REFERENCES `Perfiles`(`id_perfiles`);

ALTER TABLE `Propuestas` ADD CONSTRAINT `Propuestas_fk0` FOREIGN KEY (`id_tipospropuestas`) REFERENCES `TiposPropuestas`(`id_tipospropuestas`);

ALTER TABLE `Propuestas` ADD CONSTRAINT `Propuestas_fk1` FOREIGN KEY (`id_usuarios`) REFERENCES `Usuarios`(`id_usuarios`);

ALTER TABLE `Propuestas` ADD CONSTRAINT `Propuestas_fk2` FOREIGN KEY (`id_ambitospropuestas`) REFERENCES `AmbitosPropuestas`(`id_ambitospropuestas`);

ALTER TABLE `Acuerdos` ADD CONSTRAINT `Acuerdos_fk0` FOREIGN KEY (`id_propuestas`) REFERENCES `Propuestas`(`id_propuestas`);

ALTER TABLE `Acuerdos` ADD CONSTRAINT `Acuerdos_fk1` FOREIGN KEY (`id_usuarios2`) REFERENCES `Usuarios`(`id_usuarios`);

ALTER TABLE `Acuerdos` ADD CONSTRAINT `Acuerdos_fk2` FOREIGN KEY (`id_estadosacuerdos1`) REFERENCES `EstadosAcuerdos`(`id_estadosacuerdos`);

ALTER TABLE `Acuerdos` ADD CONSTRAINT `Acuerdos_fk3` FOREIGN KEY (`id_estadosacuerdos2`) REFERENCES `EstadosAcuerdos`(`id_estadosacuerdos`);

ALTER TABLE `Comentarios` ADD CONSTRAINT `Comentarios_fk0` FOREIGN KEY (`id_acuerdos`) REFERENCES `Acuerdos`(`id_acuerdos`);

ALTER TABLE `Comentarios` ADD CONSTRAINT `Comentarios_fk1` FOREIGN KEY (`id_usuarios`) REFERENCES `Usuarios`(`id_usuarios`);

ALTER TABLE `Eventos` ADD CONSTRAINT `Eventos_fk0` FOREIGN KEY (`id_usuarios`) REFERENCES `Usuarios`(`id_usuarios`);

ALTER TABLE `MensajesPropuestas` ADD CONSTRAINT `MensajesPropuestas_fk0` FOREIGN KEY (`id_propuestas`) REFERENCES `Propuestas`(`id_propuestas`);

ALTER TABLE `MensajesPropuestas` ADD CONSTRAINT `MensajesPropuestas_fk1` FOREIGN KEY (`id_usuarios`) REFERENCES `Usuarios`(`id_usuarios`);

