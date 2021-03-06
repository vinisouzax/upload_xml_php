create schema carebr;

CREATE TABLE `xml` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CNPJ` varchar(45) DEFAULT NULL,
  `nProt` varchar(45) DEFAULT NULL,
  `nNF` varchar(45) DEFAULT NULL,
  `dhEmi` varchar(45) DEFAULT NULL,
  `vNF` varchar(45) DEFAULT NULL,
  `destCPF` varchar(45) DEFAULT NULL,
  `xNome` varchar(100) DEFAULT NULL,
  `xLgr` varchar(100) DEFAULT NULL,
  `nro` varchar(100) DEFAULT NULL,
  `xCpl` varchar(45) DEFAULT NULL,
  `xBairro` varchar(45) DEFAULT NULL,
  `cMun` varchar(45) DEFAULT NULL,
  `UF` varchar(45) DEFAULT NULL,
  `CEP` varchar(45) DEFAULT NULL,
  `cPais` varchar(45) DEFAULT NULL,
  `xMun` varchar(45) DEFAULT NULL,
  `nomeXml` varchar(100) DEFAULT NULL,
  `destCNPJ` varchar(45) DEFAULT NULL,
  `IE` varchar(45) DEFAULT NULL,
  `indIEDest` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `fone` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8