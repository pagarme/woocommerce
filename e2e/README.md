Testes E2E para o Woocommerce
Esse folder, contém os testes que foram pensados para validar fluxos de negócio para o plugin.

Instalação
É preciso ter node instalado na maquina, para instalar, basta executar no terminal, o seguinte comando: npm install Caso tenha docker instalado na máquina, Bastar efetuar o build da imagem e utilizar. Build: docker build -t playwright_woocommerce . Executar os testes: docker run -it --rm -e URL=http://site.para.teste.br -e PRODUCT=algum_produto playwright_woocommerce

Execução dos Testes:
No arquivo package.json existem algumas execuções para serem feitas. Para qualquer execução, é preciso informar duas variaveis de ambiente:

URL=http://site.para.teste.br : url alvo para os testes serem executados. PRODUCT=algum_produto : Informar o produto que será pesquisado. Preferivel informar um produto que não tenha conflito para apresenta um unico produto.

URL=http://site.para.teste.br PRODUCT=algum_produto npm run test:e2e : executa os testes, com o que está definido de configuração. URL=http://site.para.teste.br PRODUCT=algum_produto npm run test:e2e:headed : Mesma execução acima, mas abre o navegador na máquina. npm run allure:generate : gerar um relatório de execução com o allure report npm run allure:open : abrir o relatório que foi gerado.
