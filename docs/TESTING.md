# 🧪 Como Executar os Testes

## Execução Rápida com Scripts de Teste

O projeto oferece múltiplas formas de executar testes de forma rápida e organizada.

### 1. Script Bash `tt` (Mais Simples)
```bash
# Testes rápidos (core + search básico)
./tt

# Todos os testes
./tt all

# Testes por categoria
./tt core        # Testes básicos e booleanos
./tt search      # Testes de busca textual
./tt filters     # Testes de filtros e where
./tt utilities   # Testes de métodos utilitários (inclui analyze() e cache)
```

### 2. Script PHP `t.php` (Mais Detalhado)
```bash
# Testes rápidos
php t.php quick

# Todos os testes
php t.php all

# Testes por categoria
php t.php core
php t.php search
php t.php filters
php t.php utilities
php t.php aggregations
php t.php geographic
php t.php sorting
php t.php validation
```

### 3. Via Makefile (Para Quem Prefere Make)
```bash
make test         # Teste rápido (padrão)
make test-all     # Todos os testes
make test-core    # Testes do core
make test-search  # Testes de busca
make test-filters # Testes de filtros
```

### Exemplos de Saída
```
🧪 QueryCraft (quick)
====================
BasicQueryTest: ✅ 10/10 (100%) [18ms]
TextSearchTest: ✅ 15/15 (100%) [19ms]

🎯 RESUMO: 25/25 (100%) em 37ms
🎉 PERFEITO!
```

```
🧪 QueryCraft (all)
====================
BasicQueryTest: ✅ 10/10 (100%) [18ms]
TextSearchTest: ✅ 15/15 (100%) [19ms]
BooleanQueryTest: ✅ 12/12 (100%) [19ms]
NestedQueryTest: ✅ 15/15 (100%) [26ms]
StaticClauseTest: ✅ 15/15 (100%) [20ms]
DeveloperExperienceTest: ✅ 28/28 (100%) [21ms]
FuzzyWildcardTest: ✅ 15/15 (100%) [28ms]
RangeTermTest: ✅ 16/16 (100%) [22ms]
WhereClauseTest: ✅ 17/17 (100%) [21ms]
UtilityMethodsTest: ✅ 41/41 (100%) [21ms]
AggregationTest: ✅ 16/16 (100%) [21ms]
GeographicTest: ✅ 15/15 (100%) [24ms]
SortingTest: ✅ 16/16 (100%) [21ms]
ComprehensiveValidationTest: ✅ 30/30 (100%) [17ms]

🎯 RESUMO: 261/261 (100%) em 297ms
🎉 PERFEITO!
```

## Execução Manual de Testes Individuais

Se preferir executar testes específicos manualmente:

```bash
# Teste específico
php tests/Unit/Core/BasicQueryTest.php

# Teste das novas funcionalidades (analyze + cache)
php tests/Unit/Utilities/UtilityMethodsTest.php

# Testes de busca
php tests/Unit/Search/TextSearchTest.php
```

## Categorias Disponíveis

- **quick**: Testes básicos e de busca (mais rápido)
- **core**: Testes fundamentais (BasicQuery, BooleanQuery, NestedQuery, StaticClause)
- **search**: Testes de busca textual (TextSearch, FuzzyWildcard)
- **filters**: Testes de filtros (RangeTerm, WhereClause)
- **utilities**: Testes de métodos utilitários (inclui as novas funcionalidades)
- **aggregations**: Testes de agregações
- **geographic**: Testes geográficos
- **sorting**: Testes de ordenação
- **validation**: Testes de validação
- **all**: Todos os testes disponíveis

## Status dos Testes Atualizado (Agosto 2025)

Após execução completa dos testes:

### 📊 Estatísticas Completas
- ✅ **Total**: 261/261 testes (100%)
- ⚡ **Tempo de Execução**: 297ms
- 🎯 **Taxa de Sucesso**: 100%

### 📋 Detalhamento por Categoria
- ✅ BasicQueryTest: 10/10 (100%)
- ✅ TextSearchTest: 15/15 (100%)
- ✅ BooleanQueryTest: 12/12 (100%)
- ✅ NestedQueryTest: 15/15 (100%)
- ✅ StaticClauseTest: 15/15 (100%)
- ✅ DeveloperExperienceTest: 28/28 (100%)
- ✅ FuzzyWildcardTest: 15/15 (100%)
- ✅ RangeTermTest: 16/16 (100%)
- ✅ WhereClauseTest: 17/17 (100%)
- ✅ UtilityMethodsTest: 41/41 (100%)
- ✅ AggregationTest: 16/16 (100%)
- ✅ GeographicTest: 15/15 (100%)
- ✅ SortingTest: 16/16 (100%)
- ✅ ComprehensiveValidationTest: 30/30 (100%)

### 🏆 Destacas do UtilityMethodsTest

O `UtilityMethodsTest` possui 41 testes abrangendo:

**Métodos de Paginação (Tests 1-8):**
- `size()`, `limit()`, `take()` - definição de tamanho de resultados
- `from()`, `offset()`, `skip()` - definição de offset
- `paginate()` - paginação com cálculo automático

**Métodos de Controle de Output (Tests 9-13):**
- `source()` - controle de campos retornados
- `highlight()` - destaque de texto
- `timeout()` - timeout de query

**Métodos Condicionais (Tests 14-18):**
- `when()` - execução condicional
- `unless()` - execução condicional inversa
- `scriptScore()` - pontuação customizada

**Escopos Predefinidos (Tests 19-23):**
- `active()` - filtra status ativo
- `published()` - filtra publicados
- `recent()` - filtra recentes

**Validações de Entrada (Tests 24-27):**
- Validação de valores negativos
- Validação de parâmetros inválidos
- Validação de arrays vazios

**Análise e Cache (Tests 28-33):**
- `analyze()` - análise de complexidade de query
- Cache de cláusulas estáticas
- Performance e otimização

### 🚀 Performance
- Execução ultra-rápida em **~297ms**
- Ideal para desenvolvimento contínuo
- Suporte a execução paralela de categorias
