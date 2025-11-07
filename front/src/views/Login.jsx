import { Button, Input, message } from "antd";
import { useState } from "react";
import axiosClient from "../axios-client";
import { useStateContext } from "../contexts/ContextProvider";
import { Link } from 'react-router-dom';



function Login() {
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  });
  const { setToken, setUser } = useStateContext();
  const [messageApi, contextHolder] = message.useMessage();
  function notif(type, title, message) {
    messageApi.open({
      type: type,
      title: title,
      content: message,
    });
  }

  async function formValidation() {
    const response = await axiosClient.post('/auth/login', formData);
    if (response.status === 200 || response.status === 204) {
      const data = await response.data
      setUser(data.user);
      setToken(data.token);
    } else if (response.status === 401) {
      const data = await response.data;
      notif('error', 'Erreur', data.message)
    } else {
      notif('error', 'Erreur', 'Une erreur est survenue lors de l\'authentification')
    }
  }
  return (
    <div className="w-full min-h-screen flex flex-col justify-start items-center bg-gray-100">
      {contextHolder}
      <img src="Logo-Wizia-1.png" className="max-h-30 my-10" />
      <div className="flex flex-col max-w-[410px] min-w-[350px] px-5 py-5">
        <div className="flex flex-col my-3">
          <label>Email </label>
          <Input variant="filled" placeholder="example@gmail.com" value={formData.email} onChange={(ev) => { setFormData((prev) => ({ ...prev, email: ev.target.value })) }} />
        </div>
        <div className="flex flex-col my-3">
          <label>Mot de passe </label>
          <Input.Password
            variant="filled"
            placeholder="mot de passe"
            value={formData.password}
            onChange={(ev) => { setFormData((prev) => ({ ...prev, password: ev.target.value })) }}
          />
        </div>
        <div className="flex flex-row justify-end my-3">
          <Link to="/signup">Première connexion ?</Link>
        </div>
        <div className="flex flex-row justify-end items-center my-3">
          <Button onClick={() => { formValidation() }}>Continuer</Button>
        </div>
      </div>
    </div>
  );
}

export default Login;