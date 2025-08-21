# QueryCraft - Comandos Rápidos
# 
# Uso:
#   make test          # Teste rápido
#   make test-all      # Todos os testes  
#   make test-core     # Apenas core
#   make test-search   # Apenas busca
#   make test-filters  # Apenas filtros

.PHONY: test test-all test-core test-search test-filters test-quick help

# Teste padrão (rápido)
test:
	@php t.php quick

# Testes por categoria  
test-all:
	@php t.php all

test-core:
	@php t.php core

test-search:
	@php t.php search

test-filters:
	@php t.php filters

test-quick:
	@php t.php quick

# Help
help:
	@echo "QueryCraft - Comandos de Teste"
	@echo "=============================="
	@echo ""
	@echo "🚀 Comandos Rápidos:"
	@echo "  make test         - Teste rápido (padrão)"
	@echo "  make test-all     - Todos os testes"
	@echo "  make test-core    - Testes do core"
	@echo "  make test-search  - Testes de busca"
	@echo "  make test-filters - Testes de filtros"
	@echo ""
	@echo "📋 Comandos Alternativos:"
	@echo "  php t.php quick   - Ultra-rápido (25 testes)"
	@echo "  ./tt              - Script bash simples"
	@echo ""
	@echo "📚 Documentação:"
	@echo "  docs/TESTING.md   - Guia completo de testes"
	@echo "  docs/README.md    - Índice da documentação"
	@echo ""
	@echo "⏱️  Tempos aproximados:"
	@echo "  quick: ~50ms | core: ~90ms | all: ~200ms"

# Padrão
.DEFAULT_GOAL := test
