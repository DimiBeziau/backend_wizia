import { useEffect, useState } from "react";
import Header from "../components/Header";
import CardPost from "../components/CardPost";
import HelloUser from "../components/HelloUser";
import { useStateContext } from "../contexts/ContextProvider";
import Card from "../components/ui/Card";
import LargeButton from "../components/ui/LargeButton";
import FilterTag from "../components/ui/FilterTag";
import TagPost from "../components/ui/TagPost";
import ImgPost from "../components/ui/ImgPost";
import BtnCardVoirPlus from "../components/ui/BtnCardVoirPlus";

const fakeData = [
  {
    "datePost": "2026-01-24T10:30:00Z",
    "idUser": 1001,
    "user": {
      "first_name": "Marie",
      "last_name": "Dubois",
      "email": "marie.dubois@example.com",
      "password": "motdepasse123"
    },
    "url": "https://example.com/images/post1.jpg",
    "post": "Découvrez les dernières tendances en développement web ! Cette nouvelle approche révolutionne la façon dont nous concevons les interfaces utilisateur. #WebDev #Innovation",
    "titrePost": "Les nouvelles tendances du développement web en 2025",
    "network": "linkedin",
    "isValidated": 1,
    "isPublished": 1,
    "likes": 8,
    "comments": 4
  },
  {
    "datePost": "2026-01-24T14:45:30Z",
    "idUser": 1002,
    "user": {
      "first_name": "Pierre",
      "last_name": "Martin",
      "email": "pierre.martin@example.com",
      "password": "password456"
    },
    "url": "https://example.com/images/post2.png",
    "post": "Notre équipe a lancé un nouveau produit ! Très fier de ce que nous avons accompli ensemble. Merci à tous les collaborateurs pour leur engagement.",
    "titrePost": "Lancement de notre nouveau produit innovant",
    "network": "facebook",
    "isValidated": 0,
    "isPublished": 0,
    "likes": 9,
    "comments": 1
  },
  {
    "datePost": "2026-01-23T16:20:15Z",
    "idUser": 1003,
    "user": {
      "first_name": "Sophie",
      "last_name": "Leroy",
      "email": "sophie.leroy@example.com",
      "password": "secure789"
    },
    "url": "https://example.com/images/post3.jpg",
    "post": "Moment inspirant lors de notre conférence aujourd'hui ✨ #Inspiration #Business #Success",
    "titrePost": "Retour sur une journée exceptionnelle",
    "network": "instagram",
    "isValidated": 1,
    "isPublished": 1,
    "likes": 1000,
    "comments": 112
  },
  {
    "datePost": "2026-01-22T09:10:45Z",
    "idUser": 1004,
    "user": {
      "first_name": "Thomas",
      "last_name": "Bernard",
      "email": "thomas.bernard@example.com",
      "password": "mypassword321"
    },
    "url": "https://example.com/images/post4.jpg",
    "post": "Partage d'expérience sur l'automatisation des processus métier. Comment Make.com nous a aidés à optimiser notre workflow et gagner en productivité.",
    "titrePost": "L'automatisation : un levier de croissance essentiel",
    "network": "linkedin",
    "isValidated": 1,
    "isPublished": 1,
    "likes": 5,
    "comments": 3
  },
  {
    "datePost": "2026-01-30T18:35:20Z",
    "idUser": 1005,
    "user": {
      "first_name": "Julie",
      "last_name": "Moreau",
      "email": "julie.moreau@example.com",
      "password": "password2025"
    },
    "url": "https://example.com/images/post5.png",
    "post": "Célébration en équipe ! 🎉 Nous avons atteint nos objectifs trimestriels. Merci à toute l'équipe pour cette belle performance collective.",
    "titrePost": "Objectifs trimestriels atteints !",
    "network": "facebook",
    "isValidated": 1,
    "isPublished": 1,
    "likes": 82,
    "comments": 15
  }
]

function Dashboard() {
  const [postsFutureNotValidated, setPostsFutureNotValidated] = useState([])
  const [postsFutureValidated, setPostsFutureValidated] = useState([])
  const [postsPastPublished, setPostsPastPublished] = useState([])
  const [postsFuture, setPostsFuture] = useState([])
  const [isUpdated, setIsUpdated] = useState(false);
  const [filterValue, setFilterValue] = useState("all");
  const [filtredPosts, setFiltredPosts] = useState([])
  const now = new Date();
  useEffect(() => {
    const array1 = [];
    const array2 = [];
    const array3 = [];
    const array4 = [];
    fakeData.forEach(data => {
      if (new Date(data.datePost) > now && data.isValidated == 0) {
        array1.push(data);
      } else if (new Date(data.datePost) > now && data.isValidated == 1) {
        array2.push(data);
      } else if (new Date(data.datePost) < now && data.isPublished == 1) {
        array3.push(data);
      }
      if (new Date(data.datePost) > now) {
        array4.push(data);
      }
    });
    setPostsFutureNotValidated(array1.sort((a, b) => new Date(a.datePost) - new Date(b.datePost)))
    setPostsFutureValidated(array2.sort((a, b) => new Date(a.datePost) - new Date(b.datePost)))
    setPostsPastPublished(array3.sort((a, b) => new Date(a.datePost) - new Date(b.datePost)))
    setPostsFuture(array4.sort((a, b) => new Date(a.datePost) - new Date(b.datePost)))
  }, [isUpdated])

  useEffect(() => {
    if (filterValue === "all") {
      setFiltredPosts(postsFuture)
    } else if (filterValue === "isValidated") {
      setFiltredPosts(postsFutureValidated)
    } else if (filterValue === "isNotValidated") {
      setFiltredPosts(postsFutureNotValidated)
    } else if (filterValue === "facebook") {
      setFiltredPosts(postsFuture.filter(post => post.network === "facebook" && new Date(post.datePost) > now).sort((a, b) => new Date(a.datePost) - new Date(b.datePost)))
    } else if (filterValue === "instagram") {
      setFiltredPosts(postsFuture.filter(post => post.network === "instagram" && new Date(post.datePost) > now).sort((a, b) => new Date(a.datePost) - new Date(b.datePost)))
    } else if (filterValue === "linkedin") {
      setFiltredPosts(postsFuture.filter(post => post.network === "linkedin" && new Date(post.datePost) > now).sort((a, b) => new Date(a.datePost) - new Date(b.datePost)))
    }
  }, [filterValue, postsFuture, postsFutureValidated, postsFutureNotValidated, now])

  const { user } = useStateContext()

  console.log(user)
  return (
    <div className="flex flex-col items-center gap-[32px] min-h-screen w-full bg-default-bg-color p-3 pb-[80px]">
      <Header />
      <HelloUser user={user} />
      <Card>
        <div
          className="flex flex-col items-start w-full"
          style={{
            gap: 'var(--spacing-s)'
          }}
        >
          <p
            className="font-normal leading-normal w-full"
            style={{
              fontSize: 'var(--font-size-h3)',
              color: 'var(--color-default-white)'
            }}
          >
            {postsFutureNotValidated.length} post{postsFutureNotValidated.length > 1 ? 's' : ''} en attente de validation
          </p>
          <p
            className="font-light leading-normal w-full"
            style={{
              fontSize: 'var(--font-size-h3)',
              color: 'var(--color-pink-4)'
            }}
          >
            Pour les prochains jours
          </p>
        </div>
        <LargeButton
          text="Voir le calendrier"
          variant="Primary"
          onClick={() => {
            // TODO: Ajouter la logique de navigation vers les posts
            console.log('Voir les posts en attente de validation');
          }}
        />
      </Card>
      <Card>
        <div
          className="flex flex-col items-start w-full"
          style={{
            gap: 'var(--spacing-s)'
          }}
        >
          <p
            className="font-light leading-normal w-full"
            style={{
              fontSize: 'var(--font-size-h3)',
              color: 'var(--color-green-5)'
            }}
          >
            Temps économisé ce mois-ci
          </p>
          <p
            className="font-normal leading-normal w-full"
            style={{
              fontSize: 'var(--font-size-h1)',
              color: 'var(--color-default-white)'
            }}
          >
            2h30
          </p>
        </div>
      </Card>
      <div className="flex flex-col gap-[16px] md:w-[748px]">
        <h2
          className="font-normal leading-normal w-full"
          style={{
            fontSize: 'var(--font-size-h2)',
            color: 'var(--color-default-white)'
          }}
        >
          Vos prochaines publications
        </h2>
        <div className="flex flex-row flex-wrap gap-[12px]">
          <FilterTag filterValue={filterValue} setFilterValue={setFilterValue} value="all" text="Tous" />
          <FilterTag filterValue={filterValue} setFilterValue={setFilterValue} value="isValidated" text="Programmés" />
          <FilterTag filterValue={filterValue} setFilterValue={setFilterValue} value="isNotValidated" text="Brouillon" />
          <FilterTag filterValue={filterValue} setFilterValue={setFilterValue} value="facebook" text="Facebook" />
          <FilterTag filterValue={filterValue} setFilterValue={setFilterValue} value="instagram" text="Instagram" />
          <FilterTag filterValue={filterValue} setFilterValue={setFilterValue} value="linkedin" text="Linkedin" />
        </div>
        <div className="flex flex-col gap-[16px]">
          {filtredPosts?.slice(0, 3)?.map(post => {
            const postDate = new Date(post.datePost);
            const day = postDate.getDate();
            const month = postDate.toLocaleDateString('fr-FR', { month: 'long' });
            const hours = postDate.getHours().toString().padStart(2, '0');
            const minutes = postDate.getMinutes().toString().padStart(2, '0');
            const formattedDate = `${day} ${month.charAt(0).toUpperCase() + month.slice(1)} - ${hours}:${minutes}`;

            return (
              <Card key={post.idUser || post.datePost}>
                <div
                  className="flex items-center gap-[var(--spacing-m)] w-full"
                  style={{
                    gap: 'var(--spacing-m)'
                  }}
                >
                  <ImgPost variant={post.isValidated == 0 ? "brouillon" : "planifié"} />
                  <div
                    className="flex flex-col items-start flex-1"
                    style={{
                      gap: 'var(--spacing-s)'
                    }}
                  >
                    <p
                      className="font-normal leading-normal w-full"
                      style={{
                        fontSize: 'var(--font-size-h3)',
                        color: 'var(--color-default-white)'
                      }}
                    >
                      {post.titrePost}
                    </p>
                    <p
                      className="font-normal leading-normal w-full"
                      style={{
                        fontSize: 'var(--font-size-price)',
                        color: 'var(--color-default-grey-2)'
                      }}
                    >
                      {formattedDate}
                    </p>
                  </div>
                  <TagPost variant={post.isValidated == 0 ? "brouillon" : "planifié"} />
                </div>
              </Card>
            );
          })}
          <BtnCardVoirPlus text="Voir le calendrier complet" onClick={() => { console.log('* Voir le calendrier complet *') }} />
        </div>
        <div className="flex flex-col gap-[16px]">
          <h2
            className="font-normal leading-normal w-full"
            style={{
              fontSize: 'var(--font-size-h2)',
              color: 'var(--color-default-white)'
            }}
          >
            Nouvelles idées pour vous
          </h2>
          <Card>
            <div
              className="flex items-center gap-[var(--spacing-m)] w-full"
              style={{
                gap: 'var(--spacing-m)'
              }}
            >
              <ImgPost variant="planifié" img="bulb" />
              <p
                className="font-normal leading-normal flex-1"
                style={{
                  fontSize: 'var(--font-size-h3)',
                  color: 'var(--color-default-white)'
                }}
              >
                Post sur la Journée Mondiale du Théatre
              </p>
            </div>
          </Card>
        </div>

      </div>
    </div>
  );
}

export default Dashboard;