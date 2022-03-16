# Implementační dokumentace k 1. úloze do IPP 2021/2022

**Jméno a příjmení:** Michal Šmahel\
**Login:** xsmahe01

Cílem první části projektu do předmětu Principy programovacích jazyků a OPP je
zpracování zdrojového kódu v jazyce IPPcode22 a jeho převod do XML reprezentace.

## Lexikální analýza

Zpracování zdrojového kódu prováděné skriptem `parse.php` je podobné překladu
mezi dvěma programovacími jazyky. Začíná fází, kterou je možné nazvat lexikální
analýza. Ta je v tomto případě rozdělena na dvě fáze. Během první z nich probíhá
načítání hodnot (lexémů). Tuto problematiku řeší jednoduchý konečný automat.
Vstup je kvůli omezení PHP (chybí funkce `ungetc()`) nutné číst po řádcích.
Automat je však implementován tak, aby mohl pracovat se znaky. Ve druhé fázi poté
přicházejí na řadu regulární výrazy, které kontrolují načtené hodnoty a přiřazují
jim význam.

## Syntaktická analýza

Analogicky jako u již zmíněného překladu má hlavní slovo syntaktický analyzátor.
Ten postupně žádá lexikální analalyzátor o tokeny, v nichž jsou obsažené načtené
lexémy a jejich typy. Z příchozích tokenů podle syntaktických pravidel jazyka
sestavuje instrukce, které následně podrobuje další syntaktické analýze. Podle
operačního kódu se kontroluje správnost operandů.

## Generování XML reprezentace

Pokud je zpracovaná instrukce správná, je dále předána generátoru. Ten s využitím
vestavěné knihovny `DOMDocument` skládá výsledný XML kód.

## Rozšíření NVP

Každá z dříve zmíněných sekcí je implementována v rámci vlastní třídy (Scanner,
Parser, resp. Generator). Ty při komunikaci používají entitní třídy Instruction,
Argument a Token, které reprezentují stejnojmenné objekty z reálného světa.
Pro distribuci chyb se využívají výjimky. Objevuje se zde spousta pevně daných
hodnot, takže je tu zastoupeno také několik výčtových tříd.
