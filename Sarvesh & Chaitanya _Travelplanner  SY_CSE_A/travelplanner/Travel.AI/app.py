from flask import Flask, request, jsonify
from flask_cors import CORS
from planlogic import generate_plan

app = Flask(__name__)
CORS(app)

@app.route("/")
def home():
    return "Smart Travel Planner API running"

@app.route("/plan", methods=["POST"])
def plan():
    data = request.json
    city = data.get("city")
    start_date = data.get("start_date")
    end_date = data.get("end_date")
    if not city or not start_date or not end_date:
        return jsonify({"error": "Missing parameters"}), 400
    result = generate_plan(city, start_date, end_date)
    return jsonify(result)

if __name__ == "__main__":
    app.run(debug=True)