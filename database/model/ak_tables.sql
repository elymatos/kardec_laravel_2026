select tag_create(t.name, t.name_fr)
from omeka_tags t;

select item_create(i.id)
from omeka_items i;

insert into ak_relationtype(name) values ('metadata');
insert into ak_relationtype(name) values ('cetegory');

select type_create('author') as result;
select type_create('addressee') as result;
select type_create('medium') as result;
select type_create('spirit') as result;
select type_create('person') as result;
select type_create('origin') as result;
select type_create('place') as result;
select type_create('book') as result;

select instance_create(i.name) from
    (
        select distinct name from (
                                      select distinct author as name from view_metadata where author is not null
                                      union
                                      select distinct addressee as name from view_metadata where addressee is not null
                                      union
                                      select distinct medium as name from view_metadata where medium is not null
                                      union
                                      select distinct spirit as name from view_metadata where spirit is not null
                                      union
                                      select distinct person as name from view_metadata where person is not null
                                      union
                                      select distinct origin as name from view_metadata where origin is not null
                                      union
                                      select distinct place as name from view_metadata where place is not null
                                      union
                                      select distinct book as name from view_metadata where book is not null
                                  ) a)i;

select metadata_create(m.idItem,'author',m.name) from (select distinct idItem,author as name from view_metadata where author is not null) m;
select metadata_create(m.idItem,'addressee',m.name) from (select distinct idItem,addressee as name from view_metadata where addressee is not null) m;
select metadata_create(m.idItem,'medium',m.name) from (select distinct idItem,medium as name from view_metadata where medium is not null) m;
select metadata_create(m.idItem,'spirit',m.name) from (select distinct idItem,spirit as name from view_metadata where spirit is not null) m;
select metadata_create(m.idItem,'person',m.name) from (select distinct idItem,person as name from view_metadata where person is not null) m;
select metadata_create(m.idItem,'origin',m.name) from (select distinct idItem,origin as name from view_metadata where origin is not null) m;
select metadata_create(m.idItem,'place',m.name) from (select distinct idItem,place as name from view_metadata where place is not null) m;
select metadata_create(m.idItem,'book',m.name) from (select distinct idItem,book as name from view_metadata where book is not null) m;



select type_create('team') as result;

select instance_create('Alexandre Caroli') as result;
select instance_create('André Alves Fernandes') as result;
select instance_create('Brutus Abel') as result;
select instance_create('Canuto Abreu') as result;
select instance_create('Charles Kempf') as result;
select instance_create('Dmitri Cerboncini') as result;
select instance_create('Fabio Fortes') as result;
select instance_create('Flávio de Carvalho') as result;
select instance_create('Guilherme Padilha') as result;
select instance_create('Heberth Souza') as result;
select instance_create('Herve Salmon') as result;
select instance_create('Humberto Schubert Coelho') as result;
select instance_create('Iara Rosa Farias') as result;
select instance_create('Isabela Monteiro') as result;
select instance_create('Jacques Peccatte') as result;
select instance_create('Karen Couto') as result;
select instance_create('Karen Kênnia') as result;
select instance_create('Leandro Silva Pimenta') as result;
select instance_create('Luciana Farias') as result;
select instance_create('Luís Lira') as result;
select instance_create('Marcelo Gulão') as result;
select instance_create('Patrícia Saliba') as result;
select instance_create('Philippe Gilbert') as result;
select instance_create('Sara Imad') as result;
select instance_create('Silvio Chibeni') as result;
select instance_create('Valquíria Maria Cavalcante de Moura') as result;
select instance_create('Victor Saliba') as result;

select type_instance_create('team','Alexandre Caroli') as result;
select type_instance_create('team','André Alves Fernandes') as result;
select type_instance_create('team','Brutus Abel') as result;
select type_instance_create('team','Canuto Abreu') as result;
select type_instance_create('team','Charles Kempf') as result;
select type_instance_create('team','Dmitri Cerboncini') as result;
select type_instance_create('team','Fabio Fortes') as result;
select type_instance_create('team','Flávio de Carvalho') as result;
select type_instance_create('team','Guilherme Padilha') as result;
select type_instance_create('team','Heberth Souza') as result;
select type_instance_create('team','Herve Salmon') as result;
select type_instance_create('team','Humberto Schubert Coelho') as result;
select type_instance_create('team','Iara Rosa Farias') as result;
select type_instance_create('team','Isabela Monteiro') as result;
select type_instance_create('team','Jacques Peccatte') as result;
select type_instance_create('team','Karen Couto') as result;
select type_instance_create('team','Karen Kênnia') as result;
select type_instance_create('team','Leandro Silva Pimenta') as result;
select type_instance_create('team','Luciana Farias') as result;
select type_instance_create('team','Luís Lira') as result;
select type_instance_create('team','Marcelo Gulão') as result;
select type_instance_create('team','Patrícia Saliba') as result;
select type_instance_create('team','Philippe Gilbert') as result;
select type_instance_create('team','Sara Imad') as result;
select type_instance_create('team','Silvio Chibeni') as result;
select type_instance_create('team','Valquíria Maria Cavalcante de Moura') as result;
select type_instance_create('team','Victor Saliba') as result;

insert into ak_relationtype(name) values ('production');

select type_create('translation') as result;
select type_create('translation_rev') as result;
select type_create('transcription') as result;
select type_create('transcription_rev') as result;
select type_create('edition') as result;

select idItem, type, name
from (

         select idItem,'translation' as type,translation as name
         from view_items
         union
         select idItem,'translation_rev' as type,translation as name
         from view_items
         union
         select idItem,'transcription' as type,transcription as name
         from view_items
         union
         select idItem,'transcription_rev' as type,transcription as name
         from view_items
         union
         select idItem,'edition' as type,edition as name
         from view_items) p
where name is not null
order by idItem, type;


select type_create('citation_style') as result;
select instance_create('acm-sig-proceedings.csl','ACM');
select instance_create('acs-nano.csl','ACS');
select instance_create('ama.csl','AMA');
select instance_create('apa.csl','APA');
select instance_create('associacao-brasileira-de-normas-tecnicas.csl','ABNT');
select instance_create('chicago-author-date.csl','Chicago');
select instance_create('harvard-cite-them-right.csl','Harvard');
select instance_create('ieee.csl','IEEE');
select instance_create('modern-language-association.csl','MLA');
select instance_create('turabian-fullnote-bibliography.csl','Turabian');
select instance_create('vancouver.csl','Vancouver');
select type_instance_create('citation_style','acm-sig-proceedings.csl');
select type_instance_create('citation_style','acs-nano.csl');
select type_instance_create('citation_style','ama.csl');
select type_instance_create('citation_style','apa.csl');
select type_instance_create('citation_style','associacao-brasileira-de-normas-tecnicas.csl');
select type_instance_create('citation_style','chicago-author-date.csl');
select type_instance_create('citation_style','harvard-cite-them-right.csl');
select type_instance_create('citation_style','ieee.csl');
select type_instance_create('citation_style','modern-language-association.csl');
select type_instance_create('citation_style','turabian-fullnote-bibliography.csl');
select type_instance_create('citation_style','vancouver.csl');


---
--- 10/12/2024
---

select item_create(i.id)
from omeka_items i
where i.id > 300;

select type_instance_create('author','Amélie Boudet') as result;
select type_instance_create('author','Allan Kardec') as result;
select type_instance_create('author','Senhor Cordurié (G. Cordurié)') as result;
select type_instance_create('author','Allan Kardec (secretário)') as result;
select type_instance_create('author','Secretário') as result;
select type_instance_create('author','H. Georges') as result;
select type_instance_create('author','senhora Foulon') as result;
select type_instance_create('author','P.G. Leymarie') as result;
select type_instance_create('author','Senhora Rivail') as result;
select type_instance_create('author','Julien-Louis Boudet') as result;
select type_instance_create('author','Julie-Louise Seigneat de Lacombe') as result;
select type_instance_create('addressee','Allan Kardec') as result;
select type_instance_create('addressee','Pâtier') as result;
select type_instance_create('addressee','Chefe de polícia da cidade de Paris') as result;
select type_instance_create('addressee','Gassier') as result;
select type_instance_create('addressee','Francisco Antônio Pereira') as result;
select type_instance_create('addressee','Thiry') as result;
select type_instance_create('addressee','Hanin Leblanc') as result;
select type_instance_create('addressee','Sabò') as result;
select type_instance_create('addressee','Senhor Varey') as result;
select type_instance_create('addressee','Louis Jourdan') as result;
select type_instance_create('addressee','Senhora Ebert') as result;
select type_instance_create('addressee','Senhor Vincent (Sonnac)') as result;
select type_instance_create('addressee','Senhora Lair') as result;
select type_instance_create('addressee','Senhor Augustin Babin (M. Aug. Babin)') as result;
select type_instance_create('addressee','R. Bermoy') as result;
select type_instance_create('addressee','Senhora Bouillant') as result;
select type_instance_create('addressee','Senhor Houat (Pau)') as result;
select type_instance_create('addressee','Senhor Villeneuve') as result;
select type_instance_create('addressee','Senhor Jourdan') as result;
select type_instance_create('addressee','Senhor Dumas') as result;
select type_instance_create('addressee','Senhor Dalmazzo') as result;
select type_instance_create('addressee','Senhor Matrat') as result;
select type_instance_create('addressee','Senhor Dijoud') as result;
select type_instance_create('addressee','Senhor Boudou (Toulouse)') as result;
select type_instance_create('addressee','Senhora Forge (Nantes)') as result;
select type_instance_create('addressee','Senhor Plenet (Milão)') as result;
select type_instance_create('addressee','Senhor Sabò') as result;
select type_instance_create('addressee','Senhor Edoux') as result;
select type_instance_create('addressee','Amélie Boudet') as result;
select type_instance_create('addressee','Senhor Ganipel') as result;
select type_instance_create('addressee','Senhor Rousset') as result;
select type_instance_create('addressee','Senhor Lourrain') as result;
select type_instance_create('addressee','Philippe') as result;
select type_instance_create('addressee','Um Espírito') as result;
select type_instance_create('addressee','Marechal Vaillans') as result;
select type_instance_create('medium','A. Desliens') as result;
select type_instance_create('medium','Senhora Caussin') as result;
select type_instance_create('medium','Senhor A. Didier') as result;
select type_instance_create('medium','Senhor Desliens') as result;
select type_instance_create('medium','Senhorita Auget Chédaux') as result;
select type_instance_create('medium','Senhora Delanne') as result;
select type_instance_create('medium','Senhora Auzon') as result;
select type_instance_create('medium','Senhora Henry') as result;
select type_instance_create('medium','Cazemajour') as result;
select type_instance_create('medium','Senhor Rul') as result;
select type_instance_create('medium','Senhora Costel') as result;
select type_instance_create('medium','Cabry') as result;
select type_instance_create('medium','Senhor Rubio') as result;
select type_instance_create('medium','Senhora H') as result;
select type_instance_create('medium','Senhora Béguet') as result;
select type_instance_create('medium','Senhor d’Ambel') as result;
select type_instance_create('spirit','Saül') as result;
select type_instance_create('spirit','Georges') as result;
select type_instance_create('spirit','Alfred Michell (Mitchell)') as result;
select type_instance_create('spirit','Montaigne') as result;
select type_instance_create('spirit','Didier') as result;
select type_instance_create('spirit','Lamennais') as result;
select type_instance_create('spirit','Sanson') as result;
select type_instance_create('spirit','Jobard') as result;
select type_instance_create('spirit','Demeure') as result;
select type_instance_create('spirit','Vianney, cura d’Ars') as result;
select type_instance_create('spirit','Galileu Galilei') as result;
select type_instance_create('spirit','Moki') as result;
select type_instance_create('spirit','Pierre-Joseph Proudhon') as result;
select type_instance_create('spirit','Jean Carrière') as result;
select type_instance_create('spirit','Lázaro') as result;
select type_instance_create('spirit','Maria Hurtel') as result;
select type_instance_create('spirit','Mardochée') as result;
select type_instance_create('spirit','Santo  Agostinho') as result;
select type_instance_create('spirit','Espírito simpático') as result;
select type_instance_create('spirit','Voltaire') as result;
select type_instance_create('spirit','Erasto') as result;
select type_instance_create('spirit','Agostinho') as result;
select type_instance_create('person','Rey,Gassier, de Paris') as result;
select type_instance_create('person','Didier') as result;
select type_instance_create('person','Crezel,São Luís,Bonnier') as result;
select type_instance_create('person','Lescot') as result;
select type_instance_create('person','Godu') as result;
select type_instance_create('person','Hypolite Léon Denizard Rivail, Sociedade Parisiense de Estudos Espíritas,Allan Kardec') as result;
select type_instance_create('person','Sociedade Espírita de Paris,Senhor Ebert') as result;
select type_instance_create('person','Sociedade Espírita de Paris,Doutor Chaigneau,Hillaire') as result;
select type_instance_create('person','Senhor Chaigneau') as result;
select type_instance_create('person','Antoine Demeure') as result;
select type_instance_create('person','Rousart') as result;
select type_instance_create('person','Senhor Lair,madame Allan Kardec') as result;
select type_instance_create('person','Allan Kardec') as result;
select type_instance_create('person','Senhor Morin,Senhor Bouillant') as result;
select type_instance_create('person','Senhora Foulon,Senhor Tailleur,Amélie Boudet,Senhora Rousset') as result;
select type_instance_create('person','Senhora Villeneuve') as result;
select type_instance_create('person','Senhor Grelez,Senhora Grelez,Filha do Senhor Grelez,MonSenhor d’Argel') as result;
select type_instance_create('person','Sociedade Espírita de Turim') as result;
select type_instance_create('person','Sociedade Espírita de Turim,Senhor Henri Dalmazzo (tipógrafo),Senhora marquesa Rosalès (Milão)') as result;
select type_instance_create('person','Senhor Barbaut de la Motte') as result;
select type_instance_create('person','Abade Barricand,Senhor Villon') as result;
select type_instance_create('person','Senhor Cordier,Hypolite (Hippolite)') as result;
select type_instance_create('person','Senhor Prouhet') as result;
select type_instance_create('person','Senhora Guillou,Senhor Roffin,Senhora Rousset,Rei Henrique V,Senhor Prêle filho') as result;
select type_instance_create('person','Florentin') as result;
select type_instance_create('person','Henri') as result;
select type_instance_create('person','Locke,Helvétius,Rousseau') as result;
select type_instance_create('person','Sociedade de Paris (Sociedade Parisiense de Estudos Espíritas)') as result;
select type_instance_create('person','Madame Leymarie,Jane,Senhora Allan Kardec,Sociedade (Sociedade Parisiense de Estudos Espíritas)') as result;
select type_instance_create('person','abade Barricand,Sociedade de Paris') as result;
select type_instance_create('person','Sociedade de Paris') as result;
select type_instance_create('person','Sociedade Anônima,Caixa Geral e Central do Espiritismo,Senhor Monvoisin,Senhor Allan Kardec,Joana d’Arc,Madame Rivail Allan Kardec') as result;
select type_instance_create('person','Senhor Rivail,Senhorita Amélie Boudet,Senhora Musset,Senhor Boudet,Senhora Boisset,Senhora Musset,Hypolite Léo,Jean Baptiste-Antoine Riv,Jeane Louise Duhamel') as result;
select type_instance_create('person','Senhorita Amélie Boudet,Senhor Rivail,Senhor Musset') as result;
select type_instance_create('person','Senhor Rivail, pai,Senhorita Amélie Boudet,Senhor Boudet,Senhora Musset ,Senhor Musset ,Senhora Riva') as result;
select type_instance_create('person','Senhora Rivail,Rigolet') as result;
select type_instance_create('person','Senhor Boudet,Senhorita Motteveaux,Senhor Brunel,Senhor Musset,Senhor Rigolet,Senhora Rigolet,Senhorita De') as result;
select type_instance_create('person','Senhora Rivail,Cônsul francês,Colégio Louis-le-Grand,Parlamento,Senhor Miller,Senhora Mill') as result;
select type_instance_create('person','Senhora Mathevot,Senhor Girardin,Senhor Favre-Gilly,Senhor Quinton,Senhor Laborie') as result;
select type_instance_create('person','Senhor Boudet,Amélie,Mariette,Louise') as result;
select type_instance_create('person','Amélie') as result;
select type_instance_create('person','Senhora Rivail,Félicité,Louise,Mariette,Senhor Allègre') as result;
select type_instance_create('person','Senhor Boudet,Amélie,Senhor Joyeux,Senhor Grimaldi,Senhor Demonval,Louise,Félicité,Senhora de Croze,Senhora Martin,Senhora Musset,Felicité,Pitolet,Senhor Moullins,Senhora Henr,Cassins') as result;
select type_instance_create('person','Bricarel,Pietri,de Maranale,de Monval,Senhor Charlet,Senhora Musset,Senhora Mart,Senhora de Croze,Félicité,Louise') as result;
select type_instance_create('person','Bricarel') as result;
select type_instance_create('person','Rigolet') as result;
select type_instance_create('person','Sabó,Villon') as result;
select type_instance_create('person','Conard,Deylande/Derlande') as result;
select type_instance_create('person','Aujanneau,Rey,Senhora Villon') as result;
select type_instance_create('person','Dombre,Jean de Sainte-Gemme,Sabò,Cazemajaux,Florentin Blanchard') as result;
select type_instance_create('person','Visconde de Brons,Senhor e Senhora Collignon,Senhor Gougues,Roustaing,Barbault de la Mothe,Imperador Montezuma,Amélie') as result;
select type_instance_create('person','Senhor Blanchard,Senhor Drouhet,Anaïs') as result;
select type_instance_create('person','Senhor Fornier Duplan,Senhor Marquês de Moÿ') as result;
select type_instance_create('person','Senhora Foulon,Senhora Mambaret,Bispo de Argel,Senhor d’Ambel,Arcebispo de Lyon,Erasto,Verdade,Senhor Bodier,Senhor Lachâtre,Senhora Dal Verme,Senhora Marquesa de Rosales,Senhora Collignon,Senhor Villon,Senhor Canu,Senhor Le Toullie,Amélie Boudet,Marie') as result;
select type_instance_create('person','Família Mallet,Senhor Vauchez,Henry,Chomette ,Mailleur') as result;
select type_instance_create('person','Senhor Jaubert (Carcassonne),Sociedade espírita de Bordeaux,Senhor Dombre (Marmande),Senhor Boudet,Musset') as result;
select type_instance_create('origin','Paris ') as result;
select type_instance_create('origin','Le Bégarié') as result;
select type_instance_create('origin','Labastide St. Georges') as result;
select type_instance_create('origin','Lyon') as result;
select type_instance_create('origin','Bourg') as result;
select type_instance_create('origin','Londres') as result;
select type_instance_create('origin','Valenciennes') as result;
select type_instance_create('origin','Aix-la-Chapelle') as result;
select type_instance_create('origin','Lyon,Lyon') as result;
select type_instance_create('origin','Nîmes') as result;
select type_instance_create('origin','Sète,Toulouse') as result;
select type_instance_create('origin','Marmande') as result;
select type_instance_create('origin','Bordeaux') as result;
select type_instance_create('origin','Marennes') as result;
select type_instance_create('origin','Saint-Jean-d’Angély') as result;
select type_instance_create('origin','Sainte-Adresse') as result;
select type_instance_create('origin','Bruxelas') as result;
select type_instance_create('place','Rue des Martyrs, 8, Paris') as result;
select type_instance_create('place','Quai Castellane, 31, Lyon') as result;
select type_instance_create('place','Brasil') as result;
select type_instance_create('place','Périgueux') as result;
select type_instance_create('place','Bordeaux') as result;
select type_instance_create('place','Boulogne') as result;
select type_instance_create('place','Ternes,rua Saint Ferdinand, 4, Ternes, Paris (Casa do Senhor Mansor)') as result;
select type_instance_create('place','Paris') as result;
select type_instance_create('place','Saint-Jean-d’Angely,Sonnac') as result;
select type_instance_create('place','Saint-Jean-d’Angely') as result;
select type_instance_create('place','Odéon,Teatro Francês') as result;
select type_instance_create('place','Tours,Paris') as result;
select type_instance_create('place','59 [ileg.] e Passagem Sainte-Anne (Allan Kardec),Lichtenthal') as result;
select type_instance_create('place','Rua Tables Claudiamus, 17, Lyon (R. Bermoy)') as result;
select type_instance_create('place','Rouen,Paris,Pau') as result;
select type_instance_create('place','Lavaur') as result;
select type_instance_create('place','rua de Paris, 88, Havre (Senhora Foulon),rua Basfroid, 39, subúrbio de Santo Antônio, Paris (Senhor Tailleur)') as result;
select type_instance_create('place','Argel') as result;
select type_instance_create('place','Sétif,Argel,França') as result;
select type_instance_create('place','Turim') as result;
select type_instance_create('place','Lyon') as result;
select type_instance_create('place','Sociedade de Paris,Toulouse') as result;
select type_instance_create('place','Nantes') as result;
select type_instance_create('place','Turim,Milão,rua San Domenico, 8, Turim (Senhor Henri Dalmazzo)') as result;
select type_instance_create('place','Libourne') as result;
select type_instance_create('place','Lyon,França') as result;
select type_instance_create('place','Maraus,Lyon,Bordeaux,Marennes') as result;
select type_instance_create('place','Passage Sainte-Anne,rue S.te Anne, centro') as result;
select type_instance_create('place','Suíça,Zimmerwald,Paris') as result;
select type_instance_create('place','rue de Lille, no 7, Paris (Caixa Geral e Central do Espiritismo),Paris') as result;
select type_instance_create('place','Château du Loir,Sarthe,Paris') as result;
select type_instance_create('place','Chateau-du-Loir,Sarthe') as result;
select type_instance_create('place','Rua de Sèvres, 35 (Amélie Boudet),Paris,rua du Plat, 2, Lyon (hotel do Palais Royal)') as result;
select type_instance_create('place','Château-du-Loire,Aix en Savoie,rue Saint-Côme, 4 (Senhor Rigolet),Haut Bonhomme') as result;
select type_instance_create('place','Rua de Sèvres, 35 (Amélie Boudet),Bourg,Lyon,Paris') as result;
select type_instance_create('place','Rue de Sèvres, 35 (Amélie Boudet),Inglaterra,Paris,Londres,Westminster,Tâmisa') as result;
select type_instance_create('place','Rua Tiquetonne, no 10 (Amélie Boudet),Paris') as result;
select type_instance_create('place','Château du Loir,Sarthe') as result;
select type_instance_create('place','Paris,Rue Tiquetonne, 10, Paris (Amélie Boudet),Quiévrain,Aix-la-Chapelle') as result;
select type_instance_create('place','Aachen,Rua Tiquetonne, 10, Paris (Amélie Boudet),Aix-la-Chapelle,Quiévrain,rua Montmartre,Bruxelas,Aix-la-Chape,Châte,Paris,Bélgica') as result;
select type_instance_create('place','Chateau-du-Loir,Sarthe,Alemanha') as result;
select type_instance_create('place','Château-du-Loir,Londres') as result;
select type_instance_create('place','Sarthe,Château-du-Loir,Orléans ,Tours,Félicité,Louise') as result;
select type_instance_create('place','Mâcon,Cazeneuve d’Albi,Bordeaux') as result;
select type_instance_create('place','Avignon,Sète,Toulouse,Bordeaux,avenida de Ségur') as result;
select type_instance_create('place','Lyon,Sète,Toulouse,Marmande,Bordeaux,Angoulême,Saint-Jean-d,rua d') as result;
select type_instance_create('place','Montpellier,Marmande,Nîmes,Sète,Toulouse') as result;
select type_instance_create('place','Andaluzia,Toulouse,Bordeaux,Marenne') as result;
select type_instance_create('place','avenida de Ségur,Marennes,Paris,Lyon,Hotel du Commerce lace du Chapelet, 4') as result;
select type_instance_create('place','rua S.te Anne,avenida de Ségur,Bordeaux,Bayonne,Marennes,Royan,Rochefort,Saint,Angoulême,Poitiers,Paris') as result;
select type_instance_create('place','Méchey,Marennes,Charente,Rochefort,Saint-Jean-d’Angély,Bordeaux,Angoulême,Paris') as result;
select type_instance_create('place','Paris,Angoulême,Rochefort,Marennes') as result;
select type_instance_create('place','Bélgica,França,Milão,Lyon,rue de la plage, Sainte-Adresse') as result;
select type_instance_create('place','Bruxelas,Antuérpia,Paris,rua de Moscou, 7 (Chomette),rua de la Montagne, 51, Bruxelas (Senhor Vauchez),rua de Vênus, 18, Antuérpia (Senhor Florent Gevers)') as result;
select type_instance_create('place','Carcassonne,Estados Unidos da América') as result;
select type_instance_create('book','O Livro dos Espíritos') as result;
select type_instance_create('book','O Livro dos Espíritos,Instrução Prática') as result;
select type_instance_create('book','Revista Espírita') as result;
select type_instance_create('book','O Livro dos Espíritos,O Livro dos Médiuns,Imitação do Evangelho segundo o Espiritismo,Revista Espírita') as result;
select type_instance_create('book','O Livro dos Médiuns,O Livro dos Espíritos,Revista Espírita') as result;
select type_instance_create('book','L’Echo de l’Archéologie') as result;
select type_instance_create('book','O Livro dos Espíritos,Revista Espírita') as result;
select type_instance_create('book','Viagem Espírita em 1862,Revista Espírita') as result;
select type_instance_create('book','La Vérité,A Imitação do Evangelho') as result;
select type_instance_create('book','A Imitação do Evangelho') as result;
select type_instance_create('book','Revista Espírita,Viagem Espírita') as result;
select type_instance_create('book','Revelação') as result;
select type_instance_create('book','A Gênese') as result;

select instance_create('Pierre Jouty',null) as result;
select type_instance_create('spirit','Pierre Jouty') as result;

select instance_create('Amanda Pestana', null) as result;
select type_instance_create('team','Amanda Pestana') as result;

select instance_create('Delphine de Girardin',null) as result;
select type_instance_create('spirit','Delphine de Girardin') as result;

select instance_create('Eugenie',null) as result;
select type_instance_create('medium','Eugenie') as result;

select instance_create('Hnet',null) as result;
select type_instance_create('medium','Hnet') as result;

select instance_create('Fénelon',null) as result;
select type_instance_create('spirit','Fénelon') as result;

select instance_create('Senhor Morin',null) as result;
select type_instance_create('medium','Senhor Morin') as result;

select instance_create('Mesmer',null) as result;
select type_instance_create('spirit','Mesmer') as result;

select instance_create('Fricks Philibert',null) as result;
select type_instance_create('spirit','Fricks Philibert') as result;

select instance_create('Philibert Frick',null) as result;
select type_instance_create('spirit','Philibert Frick') as result;

select instance_create('Leymarie',null) as result;
select type_instance_create('medium','Leymarie') as result;

select instance_create('Louis Henri',null) as result;
select type_instance_create('spirit','Louis Henri') as result;

select instance_create('Louis de Tourreil',null) as result;
select type_instance_create('spirit','Louis de Tourreil') as result;

select instance_create('Henri Heine',null) as result;
select type_instance_create('spirit','Henri Heine') as result;

select instance_create('Swedenborg',null) as result;
select type_instance_create('spirit','Swedenborg') as result;

select instance_create('Eyben',null) as result;
select type_instance_create('medium','Eyben') as result;

select instance_create('Flammarion',null) as result;
select type_instance_create('medium','Flammarion') as result;

select instance_create('Viellard de Boismartin',null) as result;
select type_instance_create('medium','Viellard de Boismartin') as result;

select instance_create('Bispo de [...]',null) as result;
select type_instance_create('spirit','Bispo de [...]') as result;

select instance_create('Alis d’Ambel / Abel d’Islam',null) as result;
select type_instance_create('medium','Alis d’Ambel / Abel d’Islam') as result;

select instance_create('Eugène Vezy',null) as result;
select type_instance_create('medium','Eugène Vezy') as result;

select instance_create('[Angely]',null) as result;
select type_instance_create('author','[Angely]') as result;

select instance_create('Igor Lopes', null) as result;
select type_instance_create('team','Igor Lopes') as result;

select item_create(i.id)
from omeka_items i
where i.id > 331;

select instance_create('Senhora Marty',null) as result;
select type_instance_create('medium','Senhora Marty') as result;

select instance_create('Francisco Xavier',null) as result;
select type_instance_create('spirit','Francisco Xavier') as result;

select instance_create('Senhora Gilès',null) as result;
select type_instance_create('medium','Senhora Gilès') as result;

select instance_create('Pitre - Chevalier',null) as result;
select type_instance_create('spirit','Pitre - Chevalier') as result;

select instance_create('Paul Chedeaux',null) as result;
select type_instance_create('spirit','Paul Chedeaux') as result;
