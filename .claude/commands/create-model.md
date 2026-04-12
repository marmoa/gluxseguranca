Crie um Model Eloquent para o projeto G-Lux seguindo as convenções:

## Regras obrigatórias:
1. Definir `$fillable` com todos os campos (nunca `$guarded = []`)
2. Definir `$casts` para datas, enums, booleanos e decimais
3. Incluir `use SoftDeletes` em tabelas de negócio
4. Definir todos os relacionamentos com return type
5. Adicionar Scopes para filtros recorrentes
6. Usar PHP Enums em `app/Enums/` para campos com valores fixos

## Relacionamentos do projeto (referência):

**ServiceOrder** (hub central):
- belongsTo: Client, Standard, State, City, User, ClientContract, Quote
- hasMany: ServiceOrderItem
- hasManyThrough: Inspection (via ServiceOrderItem)
- hasOne: Invoice

**Item**: belongsTo Standard, Tag | belongsToMany: Attribute, AttributeValue, Norm

**Standard**: hasMany Item | belongsToMany: Attribute (via standard_attributes)

**ServiceOrderItem**: belongsTo ServiceOrder, Item | hasMany Inspection

**Inspection**: belongsTo ServiceOrderItem | hasMany InspectionValue
- Campos: result (enum), traceability_code, rejection_code, rejection_category

**InspectionValue**: belongsTo Inspection, Attribute

**Client**: hasMany ServiceOrder, ClientContract, Invoice | belongsTo State, City

## Ao criar o Model:
- Coloque em `app/Models/`
- Crie o Enum correspondente em `app/Enums/` se aplicável
- Implemente accessors/mutators quando necessário

$ARGUMENTS
