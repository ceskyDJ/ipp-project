# Implementační dokumentace k 2. úloze do IPP 2021/2022

**Jméno a příjmení:** Michal Šmahel\
**Login:** xsmahe01

Druhá část projektu do předmětu Principy programovacích jazyků a OPP se zaměřuje
na interpretaci XML repzerentace kódu připravené skriptem z první části. Kromě
toho zahrnuje také skript pro automatické testování obou částí projektu.

## Interpret XML reprezentace

Jak již bylo naznačeno v úvodu, skript `interpret.py` navazuje na první část projektu.
Načítá zjednodušený program popsaný v jazyce XML, který již prošel lexikálními
a syntaktickými kontrolami. Díky tomu mu zbývají jen sémantické kontroly a samotná
interpretace.

### Sémantické kontroly

Mezi aplikované syntaktické kontroly patří kontrola datových typů, dělení nulou,
přístupy do paměti, inicializovanost použitých proměnných apod.

### Načítání programu do připravených struktur

Interpretace začíná načtením instrukcí z XML reprezentace do pomocných datových
struktur. Ty jsou implementované jako jednoduché entitní třídy `Argument`,
`Instruction` a `Program`. Poslední ze zmiňovaných si pak kromě instrukcí
drží informace o dostupných návěštích a jejich adresách.

### Interpretace

Interpretace je řízena třídou `Interpreter`, která si udržuje aktuální stav programu
a vykonává požadované instrukce. V jádru běží cyklus, který končí po dosažení
poslední instrukce programu. Obsahuje také sémantické kontroly, které jsou částečně
řešené obecnými metodami a částečně v metodách vykonávajících jednotlivé instrukce.

### Paměť

Při interpretaci se využívá emulovaná paměť. Emuluje se paměť s náhodným přístupem,
která poskytuje 3 druhy rámců (globální, lokální a dočasné), které jsou pro dosažení
jednoduššího a přehlednějšího rozhraní zastřešeny pomocí návrhového vzoru Facade.
Jednotlivé paměťové rámce jsou poté jen instance prosté entitní třídy, která je
univerzální pro všechny druhy rámců. Datový zásobník i zásobník volání jsou
implementovány pomocí seznamů s upraveným rozhraním pomocí vzoru Adapter. Podobně
je řešen zásobník lokálních paměťových rámců.

Paměťové rámce obsahují dynamicky typované proměnné (`Variable`). Pro zajištění
typování hodnotou, je hodnota převedena do objektového světa pomocí třídy `Value`.

### Rozšíření NVI

Celý skript je psán s využitím OOP. Jsou využity návrhové vzory Adapter a Facade
(viz výše) a dále se používají vestavěné dekorátory a iterátory jazyka Python.
Kromě toho se používají vlastní výjimky sloužící pro podrobné rozlišení detekovaných
chyb.


## Testovací skript

Třetím skriptem implementovaným v rámci projektu je tester, který automaticky
spouští předpřipravené testy a vyhodnocuje jejich průběhy. Následně sestavuje
webovou stránku plnící úlohu jednoduchého přehledu.

### Entitní třídy

Pro objektovou reprezentaci problematiky je využito několik entitních tříd.
Jedná se o `TestCase` (testovací případ k otestování), `TakenTest` (hotový test),
`TestGroup` (testy sdílející společný adresář) a `TestReport`. Z praktických
důvodů je vhodné mít skupiny testů i testovací "protokol" implementované
podle vzoru Iterator, aby bylo možné jednoduše přistupovat k obsaženým testům.

### Testování

Testování řídí objekt specifické třídy podle toho, jaký typ testování probíhá.
Pro zjednodušení vytváření těchto objektů byl využit vzor Factory. Při testování
je nutné porovnávat výstupy testovaných programů s těmi referenčními. K tomu
slouží programy zastoupené třídami, které rozhraní příkazové řádky převádějí
do objektové podoby (využití myšlenky podobné vzoru Adapter).

### Generování přehledu testů

Výstup z testování představuje HTML soubor generovaný pomocí šablonových
konstrukcí jazyka PHP a entitní třídy `TestReport`.
