Consumo de API Externa, usando Swoole e PHP puro. 
- Assicronismo na chamada das requisições.
- Concorrência.
- Utilização da API de pagamentos Asaas.
- Courotines.

Listagem de Clientes.  
- Lista todos os clientes cadastrados na plataforma de pagamentos. 
- ENDPOINT - http://127.0.0.1:9501/api/listCustomers - GET

Cadastro de Clientes. 
- Cadastra clientes na plataforma de pagamentos.
- ENDPOINT - http://127.0.0.1:9501/api/createCustomer - POST
  
      {
        "name": "Maria Helena",
        "cpfCnpj": "82407988027",
        "mobilePhone": "13743574113"
      }
  
Listagem de Cobranças .
- Lista todas as cobranças cadastradas na plataforma de pagamentos
- ENDPOINT - http://127.0.0.1:9501/api/listDebts - GET

Cadastro de Cobranças.
- Cadastra cobranças na plataforma de pagamentos.
- ENDPOINT - http://127.0.0.1:9501/api/createDebt - POST
- Customer é o ID do cliente cadastrado. Todo Pagamento precisa ser vinculado ao cliente.

      {
        "customer": "cus_000006749468",
        "billingType": "PIX",
        "value": 9687,
        "dueDate": "2025-10-02"
      }
  
