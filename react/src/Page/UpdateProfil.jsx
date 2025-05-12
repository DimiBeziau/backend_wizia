import NavBar from "../Components/Retulisatble/NavBar";
import { useState, useEffect } from "react";
import { toast, ToastContainer } from 'react-toastify';
import './Style/UpdateProfil.css';

const UpdateProfil = () => {
  const [user, setUser] = useState({
    firstName: "",
    surname: "",
    name: "",
    email: "",
    number: ""
  });

  const userId = 1;

  useEffect(() => {
    const fetchUser = async () => {
      const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}users/${userId}`);

      if (response.ok) {
        const data = await response.json();
        setUser({
          firstName: data.firstName,
          name: data.name,
          email: data.email,
          number: data.number
        });
      } else {
        toast.error("Erreur lors de la récupération du profil.");
      }
    };

    fetchUser();
  }, []);

  const handleChange = (e) => {
    setUser({
      ...user,
      [e.target.name]: e.target.value
    });
  };

  const handleUpdate = async () => {

    const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}users/${userId}`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(user),
    });

    if (response.ok) {
      toast.success("Profil mis à jour !");
    } else {
      toast.error("Erreur lors de la mise à jour.");
    }
  };

  return (
    <>
      <NavBar />
      <div className="UpdateProfil">

        <div className="card">

          <h1>Modifier le Profil</h1>

          <div className="profil-form">
            <label htmlFor="firstName">Prénom</label>
            <input
              id="firstName"
              type="text"
              name="firstName"
              value={user.firstName}
              onChange={handleChange}
            />

            <label htmlFor="name">Nom</label>
            <input
              id="name"
              type="text"
              name="name"
              value={user.name}
              onChange={handleChange}
            />

            <label htmlFor="email">Email</label>
            <input
              id="email"
              type="email"
              name="email"
              value={user.email}
              onChange={handleChange}
            />

            <label htmlFor="number">Téléphone</label>
            <input
              id="number"
              type="text"
              name="number"
              value={user.number}
              onChange={handleChange}
            />

            <button onClick={handleUpdate}>Mettre à jour</button>
          </div>

          <ToastContainer position="top-right" />
        </div>
      </div>
    </>
  );

};

export default UpdateProfil;
