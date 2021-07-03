Objetivo: 

Gerenciar as notas fiscais do cliente.

Requisitos funcionais:

    1. O sistema deve ter uma tela para realizar upload de um arquivo na extensão ".xml";
    2. O sistema deve validar se o arquivo é uma extensão .xml;
    3. O sistema deve permitir somente o upload do arquivo xml se o campo CNPJ do emitente(<emit>) for "09066241000884";
    4. O sistema deve validar se a nota possui protocolo de autorização preenchido (campo <nProt>);
    5. O sistema deve exibir em uma tela os seguintes dados: Número da nota Fiscal, Data da nota Fiscal, dados completos do destinatário e valor total da nota fiscal;

Requisitos não funcionais:

1 - Os dados que serão exibidos na tela deverão ser armazenados em um banco de dados MySQL;
2 - Deverá ser desenvolvido em linguagem PHP 7;