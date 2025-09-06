import { useState } from "react";
import Stepper from "../components/Stepper";
import Step2SignUp from "../components/Step2SignUp";
import Step1SignUp from "../components/Step1SignUp";
import Step3SignUp from "../components/Step3SignUp";
import Step4SignUp from "../components/Step4SignUp";

function Signup() {
  const [step, setStep] = useState(1);
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

  function validateForm() {
    console.log(formData);
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
  }
  return (
    <div className="w-full min-h-screen flex flex-col justify-start items-center bg-gray-100">
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