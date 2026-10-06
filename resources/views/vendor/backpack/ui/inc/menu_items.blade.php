<x-backpack::menu-item
    title="Tableau de bord"
    icon="la la-home"
    :link="backpack_url('dashboard')"
/>

<x-backpack::menu-item
    title="Localisations"
    icon="la la-map-marker"
    :link="backpack_url('location')"
/>

<x-backpack::menu-item
    title="Catégories"
    icon="la la-tags"
    :link="backpack_url('category')"
/>
<x-backpack::menu-item
    title="Faits saillants"
    icon="la la-file-text"
    :link="backpack_url('incident')"
/>

<x-backpack::menu-item
    title="Recherche avancée"
    icon="la la-search"
    :link="backpack_url('recherche')"
/>



<x-backpack::menu-item
    title="Carte des faits"
    icon="la la-map"
    :link="backpack_url('carte')"
/>