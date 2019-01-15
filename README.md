# SSG Site
Hemsida till SSG (Swedish Strategic Group) http://www.ssg-clan.se/
Webpage for SSG (Swedish Strategic Group) http://www.ssg-clan.se/
All instructions, comments and text in this projects is written in Swedish.

## Installation
1. Redigera index.php. Ändra konstanten ENVIRONMENT till **development** eller **production**.
2. Redigera databas-konfigurationsfilen:
   - Om production:
     1. Ändra namn på filen **application/config/database.default.php** till **database.php**.
     2. Redigera filen och mata in dina databas-uppgifter.
   - Om development:
     1. Skapa mappen **application/config/development**.
	 2. Flytta och byta namn på **application/config/database.default.php** till **application/config/development/database.php**
	 3. Redigera filen och mata in dina databas-uppgifter.
3. Kör filen **install.sql** i din databas.
4. Obs: I skrivandets stund (januari 2019) laddar hemsidan medlemsuppgifter och kollar inloggningar mot tabellen smf_members. Tabellen tillhandahålls av gamla sidan (SimpleMachines Forum). Hemsidan bör själv skapa placeholder-events i ssg_events en månad framåt.