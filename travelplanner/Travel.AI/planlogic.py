import datetime

def generate_transport_estimates(city):
    # Simulated values (in future: use Google or IRCTC APIs)
    modes = {
        "road": {"time_hr": 10, "fare": 700},
        "train": {"time_hr": 8, "fare": 500},
        "flight": {"time_hr": 2, "fare": 3000},
        "water": {"time_hr": 14, "fare": 1200}
    }
    # Customize based on city
    if city.lower() == "goa":
        modes["water"] = {"time_hr": 12, "fare": 900}
    elif city.lower() == "delhi":
        modes.pop("water")  # no water routes
    return modes

def generate_plan(city, start_date, end_date, preferences=None):
    days = (datetime.datetime.strptime(end_date, "%Y-%m-%d") - datetime.datetime.strptime(start_date, "%Y-%m-%d")).days + 1
    # Sample places
    places_by_city = {
        "Mumbai": ["Gateway of India", "Marine Drive", "Elephanta Caves"],
        "Goa": ["Baga Beach", "Fort Aguada", "Dudhsagar Falls"],
        "Delhi": ["Red Fort", "India Gate", "Qutub Minar"]
    }
    places = places_by_city.get(city, ["City Center", "Museum", "Park"])
    plan = []
    i = 0
    for d in range(1, days + 1):
        today = []
        for _ in range(2):
            if i < len(places):
                today.append(places[i])
                i += 1
        plan.append({"day": d, "activities": today})
    # Include transport data
    transport = generate_transport_estimates(city)
    return {
        "city": city,
        "start": start_date,
        "end": end_date,
        "days": days,
        "itinerary": plan,
        "transport": transport
    }