import time
import mysql.connector
print("start4")
# Create the connection once, outside the game_tick function
db_connection = mysql.connector.connect(
    host="185.213.26.79",
    user="clyde",
    password="PurpleHorse@01",
    database="mcg_test"
)

class GameEngine:
    def game_tick(self):
        print("in game_tick")
        # Logic to execute every game tick (1 second)
        try:
            # Create a cursor object
            cursor = db_connection.cursor()
            # Execute your query
            cursor.execute("SELECT * FROM players")
            # Fetch the results
            results = cursor.fetchall()
            # Process the results
            for row in results:
                print(row)  # or do whatever you need with each row
            # Close the cursor
            cursor.close()
        except mysql.connector.Error as error:
            print(f"Database error: {error}")
        except Exception as error:
            print(f"Error: {error}")
        print("Game tick!")
    def update_resources(self):
        # Placeholder for resource update logic
        print("Updating resources.124")
    def __init__(self):
        self.running = True

    def process_events(self):
        # Placeholder for event processing
        pass

    def update(self):
        # Placeholder for game logic update
        self.update_resources()

    def render(self):
        # Placeholder for rendering
        print("Rendering frame...")

    def run(self):
        print("Starting game engine...")
        last_tick = time.time()
        while self.running:
            self.process_events()
            self.update()
            self.render()
            current_time = time.time()
            if current_time - last_tick >= 1.0:
                self.game_tick()
                last_tick = current_time
            time.sleep(1/60)  # Run at ~60 FPS

    def stop(self):
        self.running = False

if __name__ == "__main__":
    engine = GameEngine()
    try:
        engine.run()
    except KeyboardInterrupt:
        engine.stop()
        print("Game engine stopped.")
