import { HomeFilled, CalendarFilled, PlusOutlined, LineChartOutlined } from '@ant-design/icons'
import { Link, Route } from 'react-router-dom';
function Footer({ route }) {
  return (
    <div className="fixed bottom-0 max-w-[748px] w-full h-18 text-white p-2.5">
      <div className="flex flex-row justify-around items-center bg-black w-full h-full rounded-full p-5">
        <Link to="/dashboard" className={`flex flex-row items-center justify-center rounded-full w-10 h-10 p-2 ${route === 'dashboard' ? 'bg-white w-30' : 'bg-slate-700 w-10'}`}>
          <HomeFilled style={{ color: route === 'dashboard' ? 'black' : 'white' }} className="text-2xl" />
          {route === 'dashboard' && <p className="text-black ml-1" >Dashbord</p>}
        </Link>
        <Link to="/calendar" className={`flex flex-row items-center justify-center rounded-full w-10 h-10 p-2 ${route === 'calendar' ? 'bg-white w-30' : 'bg-slate-700 w-10'}`}>
          <CalendarFilled style={{ color: route === 'calendar' ? 'black' : 'white' }} className="text-2xl" />
          {route === 'calendar' && <p className="text-black ml-1" >Calendrier</p>}
        </Link>
        <Link to="/create" className={`flex flex-row items-center justify-center rounded-full w-10 h-10 p-2 ${route === 'create' ? 'bg-white w-30' : 'bg-slate-700 w-10'}`}>
          <PlusOutlined style={{ color: route === 'create' ? 'black' : 'white' }} className="text-2xl" />
          {route === 'create' && <p className="text-black ml-1" >Créer</p>}
        </Link>
        <Link to="/stats" className={`flex flex-row items-center justify-center rounded-full w-10 h-10 p-2 ${route === 'stats' ? 'bg-white w-30' : 'bg-slate-700 w-10'}`}>
          <LineChartOutlined style={{ color: route === 'stats' ? 'black' : 'white' }} className="text-2xl" />
          {route === 'stats' && <p className="text-black ml-1" >Stats</p>}
        </Link>
      </div>
    </div >
  );
}

export default Footer;