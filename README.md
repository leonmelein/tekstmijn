![Logo Tekstmijn](https://tekstmijn.nl/mailheader.png)

# Tekstmijn: studentengedeelte
Dit is het studentengedeelte van het Tekstmijnproject. 
Dit ontsluit de volgende functionaliteiten:
*   Opdrachten inzien en inleveren;
*   Vragenlijsten inzien en invullen.

De hieruit resulterende data kunnen worden geanalyseerd via het
[stafgedeelte](https://www.github.com/leonmelein/hofstad_staff).

## Systeemvereisten
*   Voldoende vrije schijfruimte (Advies: minimaal 50 GB)
*   PHP 7.0 of hoger
*   MySQL 5.7 of hoger met `SQL_MODE=ANSI_QUOTES` ingeschakeld

## Installatie
Alvorens het studentengedeelte te installeren, 
in het zaak om het stafgedeelte eerst te installeren 
en daar studenten aan te maken, zodat deze straks
de registratie kunnen doorlopen.

Vervolgens moeten de volgende stappen uitgevoerd worden: 
1.  Maak op uw server de map _config_ aan. Plaats hierin een `.htaccess`-bestand
met `deny from all` als inhoud. Maak vervolgens een `config.ini`-bestand
aan. De inhoud hiervan is als volgt: 
```
[mysql]
server = <Adres van de MySQL Server, meestal: localhost>
database_name = <Databasenaam>
username = <Databasegebruiker>
password = <Wachtwoord atabasegebruiker>
```

2.  Plaats de bestanden uit deze repository op de server.

Hierna kunnen studenten zich registreren en gebruik 
gaan maken van het systeem.

