import { Button, Input } from "antd";
import { useState } from "react";


function Login() {
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  });
  return (
    <div className="w-full min-h-screen flex flex-col justify-start items-center bg-gray-100">
      <img src="Logo-Wizia-1.png" className="max-h-30 my-10" />
      <div className="flex flex-col max-w-[410px] px-5 py-5">
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
        <div className="flex flex-row justify-end items-center my-3">
          <Button onClick={() => { formValidation() }}>Continuer</Button>
        </div>
      </div>
    </div>
  );
}

export default Login;