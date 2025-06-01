import NavBar from "../Components/Retulisatble/NavBar";
import { useState, useEffect } from "react";
import { toast, ToastContainer } from 'react-toastify';
import './Style/UpdateProfil.css';
import { useStateContext } from "../Context/ContextProvider";
import axiosClient from "../axios-client";

const UpdateProfil = () => {
  const [users, setUsers] = useState({
    firstName: "",
    name: "",
    email: "",
    number: ""
  });

  const { user } = useStateContext();

  useEffect(() => {
    if (!user.id) return;
    console.log(user.id)
    const fetchUser = async () => {
      try {
        const { data } = await axiosClient.get(`users/${user.id}`);
        setUsers({
          firstName: data.firstName || "",
          name: data.name || "",
          email: data.email || "",
          number: data.number || ""
        });
      } catch (error) {
        toast.error("Erreur réseau lors de la récupération du profil.");
      }
    };

    fetchUser();
  }, [user.id]);

  const handleChange = (e) => {
    setUsers({
      ...users,
      [e.target.name]: e.target.value
    });
  };

  const handleUpdate = async () => {
    try {
      await axiosClient.put(`/users/${user.id}`, users);
      toast.success("Profil mis à jour !");
    } catch (error) {
      toast.error("Erreur réseau lors de la mise à jour.");
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
              value={users.firstName}
              onChange={handleChange}
            />

            <label htmlFor="name">Nom</label>
            <input
              id="name"
              type="text"
              name="name"
              value={users.name}
              onChange={handleChange}
            />

            <label htmlFor="email">Email</label>
            <input
              id="email"
              type="email"
              name="email"
              value={users.email}
              onChange={handleChange}
            />

            <label htmlFor="number">Téléphone</label>
            <input
              id="number"
              type="text"
              name="number"
              value={users.number}
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
