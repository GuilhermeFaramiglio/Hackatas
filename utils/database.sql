CREATE DATABASE HACKAMENA;

USE HACKAMENA;

CREATE TABLE empresa (
    EMP_ID INT AUTO_INCREMENT PRIMARY KEY,
    EMP_NOME VARCHAR(100) NOT NULL,
    EMP_CNPJ VARCHAR(50) NOT NULL,
    EMP_TELEFONE VARCHAR(20) NOT NULL
);

CREATE TABLE endereco (
    END_ID INT AUTO_INCREMENT PRIMARY KEY,
    END_PAIS VARCHAR(100) NOT NULL,
    END_CIDADE VARCHAR(100) NOT NULL,
    END_RUA VARCHAR(200) NOT NULL,
    END_NUMERO VARCHAR(20) NOT NULL,
    END_FK_EMPRESA_ID INT NOT NULL
);

CREATE TABLE veiculo (
    VEI_ID INT AUTO_INCREMENT PRIMARY KEY,
    VEI_MODELO VARCHAR(50) NOT NULL,
    VEI_MARCA VARCHAR(50) NOT NULL,
    VEI_PLACA VARCHAR(20) NOT NULL
);

CREATE TABLE usuario (
    USU_ID INT AUTO_INCREMENT PRIMARY KEY,
    USU_NOME VARCHAR(100) NOT NULL,
    USU_SENHA VARCHAR(100) NOT NULL
);

CREATE TABLE orcamento (
    ORC_ID INT AUTO_INCREMENT PRIMARY KEY,
    ORC_FK_EMPRESA_ID INT NOT NULL,
    ORC_FK_VEICULO_ID INT NOT NULL,
    ORC_ORIGEM VARCHAR(200) NOT NULL,
    ORC_DESTINO VARCHAR(200) NOT NULL,
    ORC_DATAINICIO DATE NOT NULL,
    ORC_DATAFIM DATE NOT NULL,
    ORC_VALOR DECIMAL(10,2) NOT NULL
);

ALTER TABLE endereco ADD CONSTRAINT FK_END_EMPRESA FOREIGN KEY (END_FK_EMPRESA_ID) REFERENCES empresa(EMP_ID);
ALTER TABLE orcamento ADD CONSTRAINT FK_ORC_EMPRESA FOREIGN KEY (ORC_FK_EMPRESA_ID) REFERENCES empresa(EMP_ID);
ALTER TABLE orcamento ADD CONSTRAINT FK_ORC_VEICULO FOREIGN KEY (ORC_FK_VEICULO_ID) REFERENCES veiculo(VEI_ID);

INSERT INTO usuario (USU_NOME, USU_SENHA)
VALUES ("admin", "admin");


-- Correção do nome da coluna de vai_placa para vei_placa
ALTER TABLE veiculo CHANGE COLUMN vai_placa vei_placa VARCHAR(20) NOT NULL;