SELECT ar.CATEGORIE, ca.DESIGNATION AS CATEGORIE, ar.CODE_ARTICLE, ar.DESIGNATION, ta.CODE_TAILLE, ta.DESIGNATION as TAILLE, co.CODE_COULEUR, co.DESIGNATION as COULEUR, sp.CODE_BARRE, sp.PRIX, sp.STOCK  
				FROM stockprix sp left join articles ar on sp.ARTICLE=ar.ID_ARTICLE 
				LEFT JOIN a_couleurs co ON sp.COULEUR = co.CODE_COULEUR 
				LEFT JOIN a_tailles ta ON sp.TAILLE = ta.CODE_TAILLE
				LEFT JOIN a_categories ca ON ar.CATEGORIE = ca.CODE_CATEGORIE
				order by ar.CATEGORIE, ar.CODE_ARTICLE, ta.CODE_TAILLE, co.DESIGNATION