function Stepper({ step }) {
  return (
    <div className="flex flex-row justify-center items-center w-full">
      <div className={`flex flex-row justify-center items-center w-15 h-15 rounded-full text-2xl text-bold ${step > 1 ? 'bg-cyan-500 text-black' : 'bg-black text-white'} transition-all duration-300`}>
        1
      </div>
      <hr className="w-12" />
      <div className={`flex flex-row justify-center items-center w-15 h-15 rounded-full text-2xl text-bold ${step < 2 ? 'bg-slate-300 text-black' : step > 2 ? 'bg-cyan-500' : 'bg-black text-white'} transition-all duration-300`}>
        2
      </div>
      <hr className="w-12" />
      <div className={`flex flex-row justify-center items-center w-15 h-15 rounded-full text-2xl text-bold ${step < 3 ? 'bg-slate-300 text-black' : step > 3 ? 'bg-cyan-500' : 'bg-black text-white'} transition-all duration-300`}>
        3
      </div>
      <hr className="w-12" />
      <div className={`flex flex-row justify-center items-center w-15 h-15 rounded-full text-2xl text-bold ${step < 4 ? 'bg-slate-300 text-black' : step > 4 ? 'bg-cyan-500' : 'bg-black text-white'} transition-all duration-300`}>
        4
      </div>
    </div >
  );
}

export default Stepper;