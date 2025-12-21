import { useState } from "react";
import Stepper from "../components/Stepper";
import Step2SignUp from "../components/Step2SignUp";
import Step1SignUp from "../components/Step1SignUp";
import Step3SignUp from "../components/Step3SignUp";
import Step4SignUp from "../components/Step4SignUp";
import axiosClient from "../axios-client";
import { useStateContext } from "../contexts/ContextProvider";
import { message } from "antd";

function Signup() {
  const [step, setStep] = useState(1);
  const { setToken, setUser } = useStateContext();
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    activity: '',
    logo: '',
    color: '#000000',
    description: '',
  })

  const [messageApi, contextHolder] = message.useMessage();
  function notif(type, title, message) {
    messageApi.open({
      type: type,
      title: title,
      content: message,
    });
  }
  async function validateForm() {
    if (!formData.first_name || !formData.last_name || !formData.email || !formData.password || !formData.password_confirmation) {
      setStep(1);
      alert("Veuillez remplir tous les champs obligatoires.");
      return;
    } else if (formData.password !== formData.password_confirmation) {
      setStep(1);
      alert("Les deux mots de passe ne correspondent pas.");
      return;
    } else if (!formData.activity) {
      setStep(2);
      alert("Veuillez remplir le champ d'activité.");
      return;
    }
    const response = await axiosClient.post('/auth/register', formData)
    if (response.status === 200 || response.status === 204) {
      console.log(response)
      const data = await response.data
      setUser(data.user)
      setToken(data.token)
    } else if (response.status === 422) {
      const data = await response.data;
      notif('error', 'Erreur', data.message)
    } else {
      notif('error', 'Erreur', 'Une erreur est survenue lors de la création du compte')
    }

  }
  return (
    <div className="w-full min-h-screen flex flex-col justify-start items-center bg-gray-100">
      {contextHolder}
      <img src="Logo-Wizia-1.png" className="max-h-30 my-10" />
      <Stepper step={step} />
      {step === 1 && (
        <Step1SignUp formData={formData} setFormData={setFormData} onNextStep={() => { setStep(2) }} />
      )}
      {
        step === 2 && (
          <Step2SignUp formData={formData} setFormData={setFormData} onPrevStep={() => { setStep(1) }} onNextStep={() => { setStep(3) }} />
        )
      }
      {
        step === 3 && (
          <Step3SignUp formData={formData} setFormData={setFormData} onPrevStep={() => { setStep(2) }} onNextStep={() => { setStep(4) }} />
        )
      }
      {
        step === 4 && (
          <Step4SignUp formData={formData} setFormData={setFormData} onPrevStep={() => { setStep(3) }} onNextStep={() => { validateForm() }} />
        )
      }
    </div>
  );
}

export default Signup;